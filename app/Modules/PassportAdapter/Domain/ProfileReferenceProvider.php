<?php
namespace Continuum\PassportAdapter\Domain;

/**
 * Passport integration boundary (03, 14). The demo uses ONLY the mock implementation.
 * A real Talentbank Passport API is REQUIRES TALENTBANK VALIDATION — no live call is made.
 */
interface ProfileReferenceProvider
{
    public function getCandidateProfileReference(int $candidateId): array;
    public function getCandidateProfileSummary(int $candidateId): array;
    /** No-op/mock until a real API exists. */
    public function requestProfileSync(int $candidateId): array;
}
