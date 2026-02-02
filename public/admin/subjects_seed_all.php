<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';
require_role(['admin'], '../login.php');

$page_title = "Seed Subjects";
$page_desc  = "Automatically add subjects for all faculties and semesters.";
$base_path  = '../';

$flash = '';
$error = '';

$plan = [
  'BSc Computing' => [
    1 => [['CS101','Programming Fundamentals'], ['CS102','Computer Systems'], ['CS103','Academic Writing'], ['CS104','Mathematics for Computing']],
    2 => [['CS201','Data Structures'], ['CS202','Database Systems'], ['CS203','Networking Basics'], ['CS204','Web Development I']],
    3 => [['CS301','Object-Oriented Programming'], ['CS302','Operating Systems'], ['CS303','Software Engineering'], ['CS304','Cyber Security Fundamentals']],
    4 => [['CS401','Algorithms'], ['CS402','Web Development II'], ['CS403','Cloud Fundamentals'], ['CS404','Human Computer Interaction']],
    5 => [['CS501','Mobile Application Development'], ['CS502','Artificial Intelligence'], ['CS503','Computer Networks II'], ['CS504','Project Management']],
    6 => [['CS601','Final Year Project'], ['CS602','Distributed Systems'], ['CS603','Information Security Management'], ['CS604','Professional Practice']],
  ],
  'BSc IT' => [
    1 => [['IT101','IT Fundamentals'], ['IT102','Programming Basics'], ['IT103','Digital Systems'], ['IT104','Communication Skills']],
    2 => [['IT201','Database Fundamentals'], ['IT202','Web Technologies'], ['IT203','Networking I'], ['IT204','Systems Analysis']],
    3 => [['IT301','Fullstack Development'], ['IT302','Operating Systems'], ['IT303','Networking II'], ['IT304','UI/UX Design']],
    4 => [['IT401','Cloud Fundamentals'], ['IT402','Cyber Security'], ['IT403','IT Service Management'], ['IT404','Data Analytics']],
    5 => [['IT501','Mobile Computing'], ['IT502','Secure Software Development'], ['IT503','DevOps and CI/CD'], ['IT504','Research Methods']],
    6 => [['IT601','Cloud Computing'], ['IT602','Cyber Security Management'], ['IT603','Final Year Project'], ['IT604','Enterprise Systems']],
  ],
  'BBA' => [
    1 => [['BBA101','Principles of Management'], ['BBA102','Business Communication'], ['BBA103','Microeconomics'], ['BBA104','Business Mathematics']],
    2 => [['BBA201','Financial Accounting'], ['BBA202','Macroeconomics'], ['BBA203','Marketing Fundamentals'], ['BBA204','Organizational Behavior']],
    3 => [['BBA301','Human Resource Management'], ['BBA302','Business Law'], ['BBA303','Cost and Management Accounting'], ['BBA304','Business Statistics']],
    4 => [['BBA401','Operations Management'], ['BBA402','Corporate Finance'], ['BBA403','Research Methods'], ['BBA404','Entrepreneurship']],
    5 => [['BBA501','Strategic Management'], ['BBA502','International Business'], ['BBA503','Business Ethics'], ['BBA504','Digital Marketing']],
    6 => [['BBA601','Final Year Project'], ['BBA602','Leadership and Change'], ['BBA603','Business Intelligence'], ['BBA604','Professional Practice']],
  ],
];

if (is_post()) {
  if (!csrf_verify()) {
    $error = "Invalid request.";
  } else {
    try {
      $pdo->beginTransaction();

      $facRows = $pdo->query("SELECT id, name FROM faculties")->fetchAll();
      $facMap = [];
      foreach ($facRows as $f) $facMap[$f['name']] = (int)$f['id'];

      $semRows = $pdo->query("SELECT id, name FROM semesters")->fetchAll();
      $semMap = [];
      foreach ($semRows as $s) $semMap[$s['name']] = (int)$s['id'];

      $ins = $pdo->prepare("
        INSERT IGNORE INTO subjects (faculty_id, semester_id, code, title)
        VALUES (?,?,?,?)
      ");

      $inserted = 0;

      foreach ($plan as $facultyName => $semesters) {
        if (!isset($facMap[$facultyName])) continue;
        $fid = $facMap[$facultyName];

        foreach ($semesters as $semNo => $subjects) {
          $semName = "Semester {$semNo}";
          if (!isset($semMap[$semName])) continue;
          $sid = $semMap[$semName];

          foreach ($subjects as $sub) {
            [$code,$title] = $sub;
            $ins->execute([$fid, $sid, $code, $title]);
            $inserted += $ins->rowCount();
          }
        }
      }

      $pdo->commit();

      $total = (int)$pdo->query("SELECT COUNT(*) AS c FROM subjects")->fetch()['c'];
      $flash = "Subjects seeded successfully. Newly added: {$inserted}. Total subjects now: {$total}.";
    } catch (Throwable $t) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      $error = "Seeding failed. Please try again.";
    }
  }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="card">
  <h2>Seed Subjects for All Faculties</h2>
  <p class="hint">
    This will insert a complete subject set for BSc Computing, BSc IT, and BBA across Semester 1 to Semester 6.
    Existing subjects will not be duplicated.
  </p>

  <?php if ($flash): ?><div class="notice ok" data-autohide="1"><?= e($flash) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="notice bad"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="subjects_seed_all.php">
    <?= csrf_input() ?>
    <div class="actions">
      <button class="btn primary" type="submit"
        data-confirm="This will add subjects for all faculties and semesters. Continue?">
        Seed All Subjects
      </button>
      <a class="btn" href="subjects.php">Back to Subjects</a>
    </div>
  </form>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>