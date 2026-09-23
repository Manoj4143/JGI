<?php
/**
 * Admin Settings Page - Change Login Illustration
 * Only accessible by logged-in admins
 */
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['is']['login']) || $_SESSION['is']['login'] !== TRUE) {
    header('Location: index.php');
    exit;
}
require_once("../includes/dbconnection.php");
$message = '';
$msgType = '';

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['login_image'])) {
    $file = $_FILES['login_image'];
    $allowed = ['image/jpeg','image/jpg','image/png','image/gif','image/webp'];
    $maxBytes = 5 * 1024 * 1024;
    if (!in_array($file['type'], $allowed)) {
        $message = 'Invalid file type. Use JPG, PNG, GIF or WEBP.';
        $msgType = 'error';
    } elseif ($file['size'] > $maxBytes) {
        $message = 'File too large. Maximum 5 MB allowed.';
        $msgType = 'error';
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $destDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
        foreach (glob($destDir . 'login_illustration.*') as $old) { @unlink($old); }
        $destFile = $destDir . 'login_illustration.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $destFile)) {
            $message = 'Login illustration updated successfully!';
            $msgType = 'success';
        } else {
            $message = 'Save failed. Check images folder permissions.';
            $msgType = 'error';
        }
    }
}

// Detect current illustration
$imgExtensions = ['jpg','jpeg','png','webp','gif'];
$illustrationSrc = '';
foreach ($imgExtensions as $ext) {
    if (file_exists(dirname(__DIR__) . '/images/login_illustration.' . $ext)) {
        $illustrationSrc = '../images/login_illustration.' . $ext;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Admin Settings &mdash; Login Illustration | Shedulo</title>
<?php include("../includes/shedulo_shell.php"); ?>
</head>
<body>
<div id="container">
<?php /* sidebar already opened in shedulo_shell.php */ ?>

  <div id="content">
    <div style="max-width:700px; margin:0 auto;">

      <!-- Page Header -->
      <div style="margin-bottom:28px;">
        <h1 style="font-family:'Plus Jakarta Sans',sans-serif; font-size:26px; font-weight:800; color:var(--app-text); margin-bottom:6px;">
          ⚙️ Admin Settings
        </h1>
        <p style="color:var(--app-text-muted); font-size:13px;">Manage global appearance settings for the Shedulo system.</p>
      </div>

      <?php if ($message): ?>
      <div style="padding:14px 20px; border-radius:14px; margin-bottom:24px; display:flex; align-items:center; gap:10px; font-size:13px; font-weight:600;
        <?php echo $msgType === 'success'
          ? 'background:rgba(16,185,129,0.12); border:1px solid rgba(16,185,129,0.35); color:#10b981;'
          : 'background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.35); color:#ef4444;'; ?>">
        <span class="material-symbols-outlined" style="font-size:18px;"><?php echo $msgType === 'success' ? 'check_circle' : 'error'; ?></span>
        <?php echo htmlspecialchars($message); ?>
      </div>
      <?php endif; ?>

      <!-- Card: Login Illustration -->
      <div style="background:var(--app-card-bg); border:1px solid var(--app-border); border-radius:20px; padding:28px; margin-bottom:24px;">
        <h2 style="font-size:16px; font-weight:700; color:var(--app-text); margin-bottom:4px;">Login Page Illustration</h2>
        <p style="font-size:12.5px; color:var(--app-text-muted); margin-bottom:20px;">
          This image appears inside the animated blob shape on the login page. Recommended: square or portrait, min 400×400px, transparent background (PNG) works best.
        </p>

        <!-- Preview row -->
        <div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap; margin-bottom:24px;">
          <!-- Current preview -->
          <div style="flex:0 0 180px;">
            <div style="font-size:11px; font-weight:700; color:var(--app-text-muted); text-transform:uppercase; letter-spacing:.6px; margin-bottom:10px;">Current Image</div>
            <div style="width:180px; height:180px; border-radius:18px; background:linear-gradient(145deg,#c8e6d0,#e8f5ef); display:flex; align-items:center; justify-content:center; overflow:hidden; border:2px solid var(--app-border); position:relative;">
              <?php if ($illustrationSrc): ?>
                <img src="<?php echo htmlspecialchars($illustrationSrc); ?>?v=<?php echo time(); ?>" alt="Current illustration" style="width:100%; height:100%; object-fit:contain; padding:10px;" id="previewImg" />
              <?php else: ?>
                <div style="text-align:center; color:var(--app-text-muted);">
                  <span class="material-symbols-outlined" style="font-size:40px; opacity:.4;">image</span>
                  <div style="font-size:11px; margin-top:4px; opacity:.5;">No image set</div>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Arrow -->
          <div style="display:flex; align-items:center; padding-top:80px; color:var(--app-text-muted);">
            <span class="material-symbols-outlined" style="font-size:28px;">arrow_forward</span>
          </div>

          <!-- New preview -->
          <div style="flex:0 0 180px;">
            <div style="font-size:11px; font-weight:700; color:var(--app-text-muted); text-transform:uppercase; letter-spacing:.6px; margin-bottom:10px;">New Image Preview</div>
            <div style="width:180px; height:180px; border-radius:18px; background:linear-gradient(145deg,#c8e6d0,#e8f5ef); display:flex; align-items:center; justify-content:center; overflow:hidden; border:2px dashed var(--app-border); position:relative;" id="newPreviewBox">
              <div id="newPreviewPlaceholder" style="text-align:center; color:var(--app-text-muted);">
                <span class="material-symbols-outlined" style="font-size:40px; opacity:.4;">upload_file</span>
                <div style="font-size:11px; margin-top:4px; opacity:.5;">Select file below</div>
              </div>
              <img id="newPreviewImg" src="" alt="" style="width:100%; height:100%; object-fit:contain; padding:10px; display:none;" />
            </div>
          </div>
        </div>

        <!-- Upload Form -->
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
          <!-- Drop zone -->
          <div id="dropZone" style="border:2px dashed var(--app-border); border-radius:14px; padding:28px 20px; text-align:center; cursor:pointer; transition:all .2s; margin-bottom:16px; position:relative;"
            onclick="document.getElementById('loginImageInput').click();"
            ondragover="event.preventDefault(); this.style.borderColor='#10b981'; this.style.background='rgba(16,185,129,0.06)';"
            ondragleave="this.style.borderColor=''; this.style.background='';"
            ondrop="event.preventDefault(); this.style.borderColor=''; this.style.background=''; handleFile(event.dataTransfer.files[0]);">
            <span class="material-symbols-outlined" style="font-size:36px; color:var(--app-text-muted); opacity:.5; display:block; margin-bottom:8px;">cloud_upload</span>
            <div style="font-size:13px; font-weight:600; color:var(--app-text-muted);">Drag &amp; drop or <span style="color:#10b981; text-decoration:underline;">browse</span></div>
            <div style="font-size:11px; color:var(--app-text-muted); margin-top:4px; opacity:.7;">JPG, PNG, WEBP, GIF &bull; Max 5 MB</div>
            <input type="file" id="loginImageInput" name="login_image" accept="image/*" style="display:none;" onchange="handleFile(this.files[0])" />
          </div>

          <button type="submit" id="uploadBtn" disabled style="
            padding:13px 28px; border:none; border-radius:999px;
            background:linear-gradient(135deg,#059669,#10b981);
            color:#fff; font-size:14px; font-weight:700;
            font-family:'Inter',sans-serif;
            cursor:pointer; opacity:.45;
            box-shadow:0 4px 16px rgba(16,185,129,0.25);
            transition:all .2s;
          ">
            <span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle; margin-right:6px;">save</span>
            Save New Illustration
          </button>
          <a href="index.php" style="margin-left:14px; font-size:13px; color:var(--app-text-muted); text-decoration:none;">
            ← Preview Login Page
          </a>
        </form>
      </div>

      <!-- Info card -->
      <div style="background:rgba(16,185,129,0.07); border:1px solid rgba(16,185,129,0.2); border-radius:14px; padding:16px 20px;">
        <div style="display:flex; gap:10px; align-items:flex-start;">
          <span class="material-symbols-outlined" style="font-size:18px; color:#10b981; margin-top:1px;">info</span>
          <div>
            <div style="font-size:13px; font-weight:700; color:var(--app-text); margin-bottom:3px;">Tips for best results</div>
            <ul style="font-size:12px; color:var(--app-text-muted); padding-left:16px; margin:0; line-height:1.8;">
              <li>Use a PNG with transparent background for cleanest result inside the blob</li>
              <li>Flat/illustrated artwork looks better than photographs</li>
              <li>Square format (1:1) or portrait (3:4) works best inside the blob shape</li>
              <li>The image automatically scales with hover zoom animation</li>
            </ul>
          </div>
        </div>
      </div>

    </div>
  </div><!-- #content -->

</div><!-- #container -->

<script>
function handleFile(file) {
  if (!file) return;
  var btn = document.getElementById('uploadBtn');
  var newImg = document.getElementById('newPreviewImg');
  var placeholder = document.getElementById('newPreviewPlaceholder');
  var reader = new FileReader();
  reader.onload = function(e) {
    newImg.src = e.target.result;
    newImg.style.display = 'block';
    placeholder.style.display = 'none';
    btn.disabled = false;
    btn.style.opacity = '1';
  };
  reader.readAsDataURL(file);
  // Also set file input value so form submits correctly
  var input = document.getElementById('loginImageInput');
  if (input.files[0] !== file) {
    var dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
  }
}
</script>
</body>
</html>
