<?php
namespace Continuum\Evidence\Domain;

use Continuum\CorePolicy\Domain\EvidenceLabel;

/**
 * Single authoritative evidence-label policy (02_NON_NEGOTIABLES.md, 05 §3).
 *
 * Responsibilities:
 *  - map a label (with confirmation/source facts) to an RER *sufficiency ceiling* 0..3;
 *  - enforce legal label transitions;
 *  - guarantee that a self-added claim cannot silently become Supported (V1 bug, 08).
 *
 * Framework-independent: no CodeIgniter dependency, so it is unit-testable standalone.
 */
final class EvidenceLabelPolicy
{
    /**
     * Effective RER sufficiency for a single requirement given the strongest evidence facts.
     *
     * @param EvidenceLabel $label          strongest label linked to the requirement
     * @param bool $candidateConfirmed       candidate confirmed an Inferred suggestion
     * @param bool $sourceLinkedAndApproved  a source is linked AND candidate-approved (Supported)
     * @param bool $humanVerified            an authorised human verified a specific source/claim
     * @return int 0..3
     */
    public function sufficiency(
        EvidenceLabel $label,
        bool $candidateConfirmed = false,
        bool $sourceLinkedAndApproved = false,
        bool $humanVerified = false
    ): int {
        return match ($label) {
            EvidenceLabel::NeedsEvidence => 0,
            EvidenceLabel::Stated        => 1,
            // Inferred is worth 1 ONLY after candidate confirmation, else 0 (05 §3).
            EvidenceLabel::Inferred      => $candidateConfirmed ? 1 : 0,
            // Supported requires source-linked AND candidate-approved.
            EvidenceLabel::Supported     => $sourceLinkedAndApproved ? 2 : 1,
            // Human Verified is 3 only with an authorised verifier. If the verifier fact is absent
            // the claim CANNOT silently earn Supported (2) credit — it degrades to what its source
            // situation actually supports (2 only if a candidate-approved source exists, else 1).
            EvidenceLabel::HumanVerified => $humanVerified ? 3 : ($sourceLinkedAndApproved ? 2 : 1),
        };
    }

    /**
     * Is a label transition permitted? Prevents illegal promotions (e.g. Stated -> Supported
     * without a candidate-approved source, or anything -> HumanVerified by the candidate).
     *
     * @param bool $byAuthorisedVerifier only an authorised human may reach HumanVerified
     */
    public function canTransition(
        EvidenceLabel $from,
        EvidenceLabel $to,
        bool $hasApprovedSource = false,
        bool $candidateConfirmed = false,
        bool $byAuthorisedVerifier = false
    ): bool {
        if ($from === $to) {
            return true;
        }
        return match ($to) {
            EvidenceLabel::NeedsEvidence => true,                       // may always downgrade
            EvidenceLabel::Stated        => true,                       // describing an approach
            EvidenceLabel::Inferred      => true,                       // system may suggest
            // Supported only with an approved source linked by the candidate.
            EvidenceLabel::Supported     => $hasApprovedSource,
            // Human Verified only by an authorised verifier (never candidate self-promotion).
            EvidenceLabel::HumanVerified => $byAuthorisedVerifier && $hasApprovedSource,
        };
    }
}
