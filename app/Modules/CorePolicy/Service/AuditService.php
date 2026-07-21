<?php
namespace Continuum\CorePolicy\Service;

/**
 * Append-only audit trail for sensitive access, consent creation/revocation and application
 * transitions (04, 09). Writes to the audit_events table via the injected DB connection.
 */
final class AuditService
{
    /** @param \CodeIgniter\Database\BaseConnection $db */
    public function __construct(private $db) {}

    public function record(
        PolicyContext $ctx,
        string $action,
        string $resourceType,
        ?string $resourceId,
        string $outcome = 'ok',
        array $meta = []
    ): void {
        $this->db->table('audit_events')->insert([
            'actor'         => $ctx->identityKey,
            'actor_role'    => $ctx->role->value,
            'action'        => $action,
            'resource_type' => $resourceType,
            'resource_id'   => $resourceId,
            'outcome'       => $outcome,
            'meta_json'     => json_encode($meta, JSON_UNESCAPED_UNICODE),
            'occurred_at'   => date('Y-m-d H:i:s'),
        ]);
    }
}
