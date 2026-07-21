<?php
namespace Continuum\Applications\Domain;

/**
 * Consent preview + snapshot construction (04_DATA_MODEL_AND_CONSENT.md, D-004).
 * Framework-independent.
 *
 * Guarantees the acceptance-critical invariant: the candidate previews EXACTLY what the employer
 * will see. Both views are rendered from the same allowed claim id set; preview_hash binds them.
 * Raw survey answers are never included.
 */
final class ConsentPreview
{
    /**
     * Build the shared evidence summary from the candidate's allowed claims only.
     *
     * @param array<int,array{id:int,signal:string,label:string,claim_text:string,source_excerpt:?string,requirement:?string}> $allClaims
     * @param int[] $allowedClaimIds
     * @return array<int,array{id:int,signal:string,label:string,claim_text:string,source_excerpt:?string,requirement:?string}>
     */
    public function summary(array $allClaims, array $allowedClaimIds): array
    {
        $allowed = array_flip($allowedClaimIds);
        $summary = [];
        foreach ($allClaims as $c) {
            if (! isset($allowed[$c['id']])) {
                continue;                       // not shared => excluded from BOTH previews
            }
            // Never leak raw survey text; only approved claim + optional source excerpt.
            $summary[] = [
                'id'             => $c['id'],
                'signal'         => $c['signal'],
                'label'          => $c['label'],
                'claim_text'     => $c['claim_text'],
                'source_excerpt' => $c['source_excerpt'] ?? null,
                'requirement'    => $c['requirement'] ?? null,
            ];
        }
        return $summary;
    }

    /**
     * Deterministic hash binding the shared summary to a role version. The employer payload is
     * later rebuilt with summary()+previewHash() and must equal this value, or CONSENT is invalid.
     */
    public function previewHash(array $summary, int $roleVersionId): string
    {
        // Canonicalise: sort by claim id, stable JSON, include role version.
        usort($summary, fn ($a, $b) => $a['id'] <=> $b['id']);
        $payload = json_encode(
            ['role_version_id' => $roleVersionId, 'summary' => $summary],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        return hash('sha256', $payload);
    }

    /** Employer view equals candidate preview iff hashes match. */
    public function matchesEmployerView(array $employerSummary, int $roleVersionId, string $candidatePreviewHash): bool
    {
        return hash_equals($candidatePreviewHash, $this->previewHash($employerSummary, $roleVersionId));
    }
}
