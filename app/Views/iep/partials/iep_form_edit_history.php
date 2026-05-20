<?php
/**
 * Section 8 — Edit history (signed IEPs, drafting teacher / admin).
 *
 * @var array<int,array<string,mixed>> $iepEditLogs
 */
$iepEditLogs = $iepEditLogs ?? [];
if (empty($iepEditLogs)) {
    return;
}
?>
<div class="card mb-4" id="iepEditHistoryPanel" style="border-left:4px solid #1e4072;">
    <div class="card-header p-0" style="background:#f8f9fa;">
        <button class="btn w-100 text-start d-flex justify-content-between align-items-center py-3 px-3 text-white border-0 rounded-0"
                type="button" data-bs-toggle="collapse" data-bs-target="#iepEditHistoryCollapse"
                style="background:#1e4072;"
                aria-expanded="false" aria-controls="iepEditHistoryCollapse">
            <span class="fw-semibold"><i class="bi bi-clock-history me-2"></i>Edit history</span>
            <i class="bi bi-chevron-down"></i>
        </button>
    </div>
    <div class="collapse" id="iepEditHistoryCollapse">
        <div class="card-body small" style="background:#fafafa;">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" style="font-family:ui-monospace,monospace;font-size:0.82rem;">
                    <thead>
                    <tr style="color:#1e4072;">
                        <th>When</th>
                        <th>Field</th>
                        <th>Old</th>
                        <th>New</th>
                        <th>By</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($iepEditLogs as $log): ?>
                        <tr>
                            <td class="text-nowrap"><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime((string) ($log['edited_at'] ?? '')))) ?></td>
                            <td><?= htmlspecialchars((string) ($log['field_name'] ?? '')) ?></td>
                            <td class="text-break" style="max-width:180px;"><?= htmlspecialchars((string) ($log['old_value'] ?? '')) ?></td>
                            <td class="text-break" style="max-width:180px;"><?= htmlspecialchars((string) ($log['new_value'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string) ($log['edited_by_name'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
