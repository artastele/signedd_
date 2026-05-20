<?php
/**
 * Centered modal: signed PDSP PDF (Process 4) + optional domain summary.
 *
 * @var array<string,mixed> $iep
 * @var array<int,array<string,mixed>> $pdspDomainRows
 * @var string $basePath
 */
$pdspDomainRows = $pdspDomainRows ?? [];
$pdspId         = (int) ($iep['pdsp_id'] ?? 0);
$hasSignedPdf   = !empty($iep['pdsp_signed_document_path']);
/** Inline display — must use /file/view/… (download URL forces attachment and triggers unwanted downloads). */
$pdfViewUrl = $hasSignedPdf && $pdspId > 0
    ? htmlspecialchars($basePath . '/file/view/pdsp_document/' . $pdspId, ENT_QUOTES, 'UTF-8')
    : '';
$pdfDownloadUrl = $hasSignedPdf && $pdspId > 0
    ? htmlspecialchars($basePath . '/file/download/pdsp_document/' . $pdspId, ENT_QUOTES, 'UTF-8')
    : '';
?>
<div class="modal fade" id="pdspRefModal" tabindex="-1" aria-labelledby="pdspRefModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-top:4px solid #1e4072;">
            <div class="modal-header text-white" style="background-color:#1e4072;">
                <h5 class="modal-title h6 mb-0" id="pdspRefModalLabel">
                    <i class="bi bi-file-earmark-pdf me-2"></i>Signed PDSP (reference)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-light">
                <?php if ($hasSignedPdf && $pdfViewUrl !== ''): ?>
                    <div class="d-flex flex-wrap gap-2 px-3 py-2 border-bottom bg-white align-items-center small">
                        <a class="btn btn-sm btn-outline-primary" href="<?= $pdfViewUrl ?>" target="_blank" rel="noopener">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Open in new tab
                        </a>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= $pdfDownloadUrl ?>">
                            <i class="bi bi-download me-1"></i>Download PDF
                        </a>
                        <span class="text-muted">Preview loads when you open this window (no auto-download).</span>
                    </div>
                    <iframe id="pdspRefIframe" title="Signed PDSP PDF" data-src="<?= $pdfViewUrl ?>"
                            style="width:100%;min-height:65vh;border:0;display:block;background:#fff;"></iframe>
                <?php else: ?>
                    <div class="p-4 text-muted small">No signed PDSP PDF is on file for this meeting.</div>
                <?php endif; ?>

                <?php if (!empty($pdspDomainRows)): ?>
                    <div class="p-3 border-top bg-white" style="max-height:240px;overflow-y:auto;font-size:0.9rem;">
                        <div class="fw-semibold mb-2" style="color:#1e4072;">PDSP domain summary</div>
                        <?php foreach ($pdspDomainRows as $dr): ?>
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="fw-semibold" style="color:#1e4072;"><?= htmlspecialchars($dr['domain_name'] ?? '') ?></div>
                                <?php if (!empty($dr['sub_domain'])): ?>
                                    <div class="text-muted small"><?= htmlspecialchars($dr['sub_domain']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($dr['skills_description'])): ?>
                                    <div class="mt-1"><?= nl2br(htmlspecialchars($dr['skills_description'])) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var modal = document.getElementById('pdspRefModal');
    if (!modal) return;
    modal.addEventListener('shown.bs.modal', function () {
        var ifr = document.getElementById('pdspRefIframe');
        if (!ifr || ifr.getAttribute('src')) return;
        var s = ifr.getAttribute('data-src');
        if (s) ifr.setAttribute('src', s);
    });
    modal.addEventListener('hidden.bs.modal', function () {
        var ifr = document.getElementById('pdspRefIframe');
        if (ifr) ifr.removeAttribute('src');
    });
})();
</script>
