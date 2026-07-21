<?php
namespace Continuum\PassportAdapter\Service;

use Continuum\PassportAdapter\Domain\ProfileReferenceProvider;

/** Returns synthetic fixtures only. Presents a "Mock Career Passport reference" in demo metadata. */
final class MockProfileReferenceProvider implements ProfileReferenceProvider
{
    public function getCandidateProfileReference(int $candidateId): array
    {
        return [
            'reference_id'        => 'MOCK-PASSPORT-' . str_pad((string) $candidateId, 4, '0', STR_PAD_LEFT),
            'source'              => 'mock',
            'data_classification' => 'synthetic_fixture',
            'note'                => 'Mock Career Passport reference. REQUIRES TALENTBANK VALIDATION.',
        ];
    }

    public function getCandidateProfileSummary(int $candidateId): array
    {
        return [
            'reference_id'        => 'MOCK-PASSPORT-' . str_pad((string) $candidateId, 4, '0', STR_PAD_LEFT),
            'data_classification' => 'synthetic_fixture',
            'summary'             => 'Synthetic profile summary for demo only.',
        ];
    }

    public function requestProfileSync(int $candidateId): array
    {
        // Intentionally a no-op. No HTTP call to Talentbank.
        return ['synced' => false, 'reason' => 'REQUIRES TALENTBANK VALIDATION'];
    }
}
