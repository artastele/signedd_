# Signature Pad Setup

## Required Library

The enrollment form uses **Signature Pad** library for digital signatures.

### Option 1: CDN (Recommended - No Installation)

Add this to your enrollment form view before `enrollment.js`:

```html
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script src="<?php echo $basePath; ?>/js/enrollment.js"></script>
```

### Option 2: Download and Host Locally

1. Download from: https://github.com/szimek/signature_pad/releases
2. Extract `signature_pad.umd.min.js`
3. Place in `public/js/signature_pad.min.js`
4. Include in your view:

```html
<script src="<?php echo $basePath; ?>/js/signature_pad.min.js"></script>
<script src="<?php echo $basePath; ?>/js/enrollment.js"></script>
```

## Usage in Form

```html
<div class="signature-container">
    <canvas id="signaturePad" width="600" height="200" style="border: 1px solid #ccc;"></canvas>
    <button type="button" onclick="clearSignature()" class="btn btn-sm btn-secondary mt-2">
        Clear Signature
    </button>
</div>
```

## JavaScript Functions Available

- `initSignaturePad(canvasId)` - Initialize signature pad
- `clearSignature()` - Clear the signature
- `getSignatureData()` - Get signature as base64 PNG
- `setSignatureData(dataURL)` - Load existing signature

## Automatic Initialization

The signature pad is automatically initialized when the page loads if a canvas with id `signaturePad` exists.

---

**Status:** Ready to use with CDN (no installation needed)
