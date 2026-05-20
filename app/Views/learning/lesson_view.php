<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-13
// Part of: SPED LMS — Lesson Viewer (Materials + Activities)

$pageTitle = htmlspecialchars($lessonPlan['title'] ?? 'Lesson') . ' — SPED LMS';
require_once __DIR__ . '/../layouts/header.php';

$basePath = defined('BASE_PATH') ? BASE_PATH : '';

function ytNocookie(string $u): string {
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $u, $m))
        return 'https://www.youtube-nocookie.com/embed/'.$m[1].'?rel=0';
    return $u;
}
function gdPreview(string $u): string {
    if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $u, $m))
        return 'https://drive.google.com/file/d/'.$m[1].'/preview';
    return $u;
}
?>
<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
<div class="container-fluid p-0">

<style>
.lv-back{display:inline-flex;align-items:center;gap:6px;color:#1e4072;text-decoration:none;font-size:.9rem;font-weight:600;margin-bottom:14px}
.lv-back:hover{color:#a01422}
.lv-domain{display:inline-block;padding:3px 10px;border-radius:20px;background:#1e4072;color:#fff;font-size:.7rem;font-weight:700}
.lv-title{font-size:1.2rem;font-weight:700;color:#1e4072;margin:6px 0 4px}
.lv-card{background:#fff;border-radius:16px;border:none;border-left:none;box-shadow:0 2px 8px rgba(0,0,0,.07);padding:18px;margin-bottom:16px}
.lv-card h2{font-size:.95rem;font-weight:700;color:#1e4072;margin:0 0 14px;display:flex;align-items:center;gap:8px}
.mitem{display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #f2f2f2}
.mitem:last-child{border-bottom:none}
.micon{width:36px;height:36px;border-radius:8px;background:#eef1f7;display:flex;align-items:center;justify-content:center;color:#1e4072;font-size:1.1rem;flex-shrink:0}
.minfo{flex:1;min-width:0}
.mtitle{font-weight:600;color:#2c2c2c;font-size:.9rem}
.mtype{font-size:.75rem;color:#6c757d;margin-bottom:4px}
.embed-wrap{margin-top:8px;border-radius:10px;overflow:hidden;background:#000}
.embed-wrap iframe{width:100%;height:220px;border:none;display:block}
.mbtn{display:inline-flex;align-items:center;gap:6px;background:#1e4072;color:#fff;border:none;border-radius:10px;padding:8px 14px;font-size:.82rem;font-weight:600;cursor:pointer;text-decoration:none;min-height:38px;flex-shrink:0}
.mbtn:hover{opacity:.85;color:#fff}
.acard{border-radius:14px;border-left:4px solid #1e4072;padding:14px 16px;margin-bottom:10px;background:#fff;box-shadow:0 2px 6px rgba(0,0,0,.06)}
.atitle{font-weight:700;color:#1e4072;font-size:.92rem;margin-bottom:6px}
.ameta{display:flex;flex-wrap:wrap;gap:6px;align-items:center;margin-bottom:10px}
.atype{display:inline-block;padding:2px 8px;border-radius:6px;font-size:.68rem;font-weight:700;color:#fff}
.atype-mc{background:#1e4072}.atype-tf{background:#3b6d11}.atype-fi{background:#a01422}
.atype-ma{background:#6c757d}.atype-dd{background:#e67e22}.atype-il{background:#8e44ad}
.atype-fc{background:#2980b9}.atype-sq{background:#16a085}
.astatus-ns{color:#6c757d;font-size:.78rem;font-weight:600}
.astatus-sub{color:#3b6d11;font-size:.78rem;font-weight:600}
.astatus-gr{color:#1e4072;font-size:.78rem;font-weight:600}
.adue{font-size:.78rem}.adue-ov{color:#a01422;font-weight:600}.adue-up{color:#e67e22}
.abtn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;min-height:48px;border-radius:20px;font-size:.9rem;font-weight:700;text-decoration:none;border:none;cursor:pointer}
.abtn:hover{opacity:.88}
.abtn--start{background:#a01422;color:#fff}.abtn--view{background:#1e4072;color:#fff}
.vbdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.72);z-index:2000;align-items:center;justify-content:center}
.vbdrop.open{display:flex}
.vmodal{background:#fff;border-radius:12px;width:90vw;max-width:820px;max-height:90vh;display:flex;flex-direction:column;overflow:hidden}
.vhead{background:#1e4072;color:#fff;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;font-weight:700;font-size:.92rem}
.vclose{background:none;border:none;color:#fff;font-size:1.3rem;cursor:pointer;padding:2px 8px;border-radius:4px}
.vclose:hover{background:rgba(255,255,255,.2)}
.vbody{flex:1;overflow:auto}
.vbody iframe{width:100%;height:70vh;border:none;display:block}
@media(max-width:768px){.embed-wrap iframe{height:180px}}
</style>

<a href="<?php echo $basePath; ?>/learning/dashboard" class="lv-back">
  <i class="bi bi-arrow-left"></i> Back to My Lessons
</a>

<span class="lv-domain"><?php echo htmlspecialchars($lessonPlan['pdsp_domain'] ?? ''); ?></span>
<div class="lv-title"><?php echo htmlspecialchars($lessonPlan['title'] ?? ''); ?></div>
<?php if (!empty($lessonPlan['school_year'])): ?>
<div style="font-size:.8rem;color:#6c757d;margin-bottom:16px;">School Year: <?php echo htmlspecialchars($lessonPlan['school_year']); ?></div>
<?php else: ?><div style="margin-bottom:16px;"></div><?php endif; ?>

<!-- Combined Tabbed Card -->
<div class="lv-card p-0" style="overflow: hidden;">
    <!-- Tabs Header -->
    <ul class="nav nav-tabs nav-fill" id="lessonTabs" role="tablist" style="background:#f8f9fa; border-bottom: 2px solid #eef1f7;">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="materials-tab" data-bs-toggle="tab" data-bs-target="#materials-tab-pane" type="button" role="tab" aria-controls="materials-tab-pane" aria-selected="true" style="color: #1e4072; border:none; padding: 16px;">
                <i class="bi bi-paperclip me-1"></i> Learning Materials
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="activities-tab" data-bs-toggle="tab" data-bs-target="#activities-tab-pane" type="button" role="tab" aria-controls="activities-tab-pane" aria-selected="false" style="color: #1e4072; border:none; padding: 16px;">
                <i class="bi bi-puzzle me-1"></i> Activities
            </button>
        </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content" id="lessonTabsContent" style="padding: 20px;">
        <!-- Materials Tab -->
        <div class="tab-pane fade show active" id="materials-tab-pane" role="tabpanel" aria-labelledby="materials-tab" tabindex="0">
            <?php if (!empty($materials)): ?>
                <?php foreach ($materials as $mat):
                    $mtype=$mat['material_type']; $etype=$mat['embed_type']??''; $murl=$mat['external_url']??'';
                ?>
                <div class="mitem">
                    <div class="micon">
                    <?php if($mtype==='file'):?><i class="bi bi-file-earmark"></i>
                    <?php elseif($mtype==='link'):?><i class="bi bi-link-45deg"></i>
                    <?php else:?><i class="bi bi-play-circle"></i><?php endif;?>
                    </div>
                    <div class="minfo">
                    <div class="mtitle"><?php echo htmlspecialchars($mat['title']);?></div>
                    <div class="mtype"><?php echo ucfirst($mtype);?><?php echo $etype?' — '.htmlspecialchars($etype):'';?></div>
                    <?php if($mtype==='embed'&&$etype==='youtube'&&$murl):?>
                        <div class="embed-wrap">
                        <iframe src="<?php echo htmlspecialchars(ytNocookie($murl));?>"
                                allow="accelerometer;encrypted-media;gyroscope;picture-in-picture"
                                allowfullscreen title="<?php echo htmlspecialchars($mat['title']);?>"></iframe>
                        </div>
                    <?php elseif($mtype==='embed'&&$etype==='gdrive'&&$murl):?>
                        <div class="embed-wrap">
                        <iframe src="<?php echo htmlspecialchars(gdPreview($murl));?>"
                                allow="autoplay" title="<?php echo htmlspecialchars($mat['title']);?>"></iframe>
                        </div>
                    <?php endif;?>
                    </div>
                    <?php if($mtype==='file'&&!empty($mat['file_path'])):?>
                    <button class="mbtn" onclick="openViewer('<?php echo htmlspecialchars($basePath.'/file/view/lesson_material/'.$mat['id']);?>','<?php echo htmlspecialchars(addslashes($mat['title']));?>')">
                        <i class="bi bi-eye"></i> View
                    </button>
                    <?php elseif(($mtype==='link'||($mtype==='embed'&&$etype==='other'))&&$murl):?>
                    <a href="<?php echo htmlspecialchars($murl);?>" target="_blank" rel="noopener noreferrer" class="mbtn">
                        <i class="bi bi-box-arrow-up-right"></i> Open
                    </a>
                    <?php endif;?>
                </div>
                <?php endforeach;?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-folder2-open text-muted" style="font-size: 2.5rem;"></i>
                    <p class="text-muted mt-2">No learning materials found for this lesson.</p>
                </div>
            <?php endif;?>
        </div>

        <!-- Activities Tab -->
        <div class="tab-pane fade" id="activities-tab-pane" role="tabpanel" aria-labelledby="activities-tab" tabindex="0">
            <?php if(empty($activities)):?>
                <div class="text-center py-5">
                    <i class="bi bi-controller text-muted" style="font-size: 2.5rem;"></i>
                    <p class="text-muted mt-2">No activities have been added to this lesson yet.</p>
                </div>
            <?php else: foreach($activities as $act):
                $sub=$act['submission']??null; $hasSub=!empty($sub['submission_id']);
                $isGraded=$hasSub&&(!empty($sub['score'])||!empty($sub['is_complete']));
                $dueTs=!empty($act['due_date'])?strtotime($act['due_date']):null;
                $isOv=$dueTs&&$dueTs<time()&&!$hasSub;
                $typeMap=['multiple_choice'=>['atype-mc','Multiple Choice'],'true_false'=>['atype-tf','True / False'],
                'fill_in_blanks'=>['atype-fi','Fill in Blanks'],'matching'=>['atype-ma','Matching'],
                'drag_drop_sort'=>['atype-dd','Sort'],'image_label'=>['atype-il','Image Label'],
                'flashcards'=>['atype-fc','Flashcards'],'sequencing'=>['atype-sq','Sequencing']];
                $tm=$typeMap[$act['activity_type']??'']??['atype-mc',ucwords(str_replace('_',' ',$act['activity_type']??''))];
            ?>
            <div class="acard">
                <div class="atitle"><?php echo htmlspecialchars($act['title']);?></div>
                <div class="ameta">
                <span class="atype <?php echo $tm[0];?>"><?php echo $tm[1];?></span>
                <?php if($isGraded):?><span class="astatus-gr"><i class="bi bi-star-fill text-warning"></i> Graded<?php if(!empty($sub['score'])):?> — <?php echo (int)$sub['score'];?>/<?php echo (int)($sub['grade_max_score']??$act['max_score']??0);?><?php endif;?></span>
                <?php elseif($hasSub):?><span class="astatus-sub"><i class="bi bi-check-circle-fill"></i> Submitted</span>
                <?php else:?><span class="astatus-ns"><i class="bi bi-circle"></i> Not started</span><?php endif;?>
                <?php if($dueTs):?><span class="adue <?php echo $isOv?'adue-ov':'adue-up';?>"><i class="bi bi-clock"></i> <?php echo $isOv?'Overdue — ':'Due ';echo date('M j, Y',$dueTs);?></span><?php endif;?>
                </div>
                <?php if($hasSub):?>
                <a href="<?php echo $basePath;?>/learning/activity/<?php echo (int)$act['id'];?>" class="abtn abtn--view"><i class="bi bi-eye"></i> View Result</a>
                <?php else:?>
                <a href="<?php echo $basePath;?>/learning/activity/<?php echo (int)$act['id'];?>" class="abtn abtn--start"><i class="bi bi-play-circle-fill"></i> Start Activity</a>
                <?php endif;?>
            </div>
            <?php endforeach; endif;?>
        </div>
    </div>
</div>

<style>
.nav-tabs .nav-link { color: #6c757d; border-radius: 0; }
.nav-tabs .nav-link.active { color: #a01422 !important; border-bottom: 3px solid #a01422 !important; background: transparent !important; }
.nav-tabs .nav-link:hover { border-color: transparent; color: #a01422; }
</style>

</div>
</div><!-- /.main-content -->

<!-- Viewer Modal -->
<div class="vbdrop" id="vDrop" onclick="closeViewerClick(event)">
  <div class="vmodal">
    <div class="vhead"><span id="vTitle">Document</span><button class="vclose" onclick="closeViewer()"><i class="bi bi-x-lg"></i></button></div>
    <div class="vbody"><iframe id="vFrame" src="" title="Document Viewer"></iframe></div>
  </div>
</div>

<script>
function openViewer(url,title){document.getElementById('vTitle').textContent=title||'Document';document.getElementById('vFrame').src=url;document.getElementById('vDrop').classList.add('open');}
function closeViewer(){document.getElementById('vDrop').classList.remove('open');document.getElementById('vFrame').src='';}
function closeViewerClick(e){if(e.target===document.getElementById('vDrop'))closeViewer();}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
