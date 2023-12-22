<?php
require 'db_config.php';
session_start();
include 'layout.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script type="text/javascript">window.location = "login.php";</script>';
    exit;
}

$userId = $_SESSION['user_id']; 
$userQuery = $conn->prepare("SELECT category FROM users WHERE id = ?");
$userQuery->bind_param('i', $userId);
$userQuery->execute();
$userCategoryResult = $userQuery->get_result();
$userCategoryData = $userCategoryResult->fetch_assoc();
$selectedCategory = $userCategoryData['category'];

$sql = "SELECT id, name FROM users WHERE category='$selectedCategory'";
$resultUsers = $conn->query($sql);
setlocale(LC_TIME, 'pl_PL.UTF-8');


$usernames = [];
while ($user = $resultUsers->fetch_assoc()) {
    $usernames[$user['id']] = $user['name'];
}

$month = date('Y-m'); 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['selected_month'])) {
    $month = $_POST['selected_month'];
}

$daysInMonth = date('t', strtotime($month . '-01'));
$schedule = [];
foreach ($usernames as $userId => $username) {
    for ($i = 1; $i <= $daysInMonth; $i++) {
        $workDate = date('Y-m-d', strtotime($month . "-$i"));
        $sql = "SELECT start_time FROM schedules WHERE user_id = '$userId' AND work_date = '$workDate'";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
            $schedule[$userId][$i] = $data['start_time'];
        } else {
            $schedule[$userId][$i] = "";
        }
    }
}

function getPolishDayOfWeek($date) {
    $englishDay = date('D', strtotime($date));
    $polishDays = [
        'Mon' => 'Pn',
        'Tue' => 'Wt',
        'Wed' => 'Śr',
        'Thu' => 'Czw',
        'Fri' => 'Pt',
        'Sat' => 'Sb',
        'Sun' => 'Nd'
    ];
    return $polishDays[$englishDay];
}
function getPolishMonthName($date) {
    $monthNumber = date('m', strtotime($date));
    $polishMonths = [
        '01' => 'Styczeń',
        '02' => 'Luty',
        '03' => 'Marzec',
        '04' => 'Kwiecień',
        '05' => 'Maj',
        '06' => 'Czerwiec',
        '07' => 'Lipiec',
        '08' => 'Sierpień',
        '09' => 'Wrzesień',
        '10' => 'Październik',
        '11' => 'Listopad',
        '12' => 'Grudzień'
    ];
    return $polishMonths[$monthNumber];
}
$monthName = getPolishMonthName($month);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.3.2/html2canvas.min.js"></script>




<style>
.table-with-margin {
    margin: 20px;
    background-color: white;
}
.downloadButton {
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        transition: background-color 0.3s ease;
        width: 150px;
        margin: 0 auto;
    }
</style>
</head>
<body>
<div class="date-form">
<h1>Pełny grafik</h1>
<form action="" method="post" class="custom-form">



    <input type="month" name="selected_month" value="<?php echo $month; ?>" required>

    <input type="submit" value="Wybierz">
</form>
</div>
<button class='downloadButton' onclick='downloadTableAsImage()'>Pobierz Grafik</button>


<form action="" method="post">


    <input type="hidden" name="selected_category" value="<?php echo $selectedCategory; ?>">

    <input type="hidden" name="schedule_submit" value="1">
    <input type="hidden" name="selected_month" value="<?php echo $month; ?>">

    <table border="1">
    <tr>
        <th><?php echo $monthName; ?></th>
        <?php foreach ($usernames as $username) : ?>
            <th><?php echo $username; ?></th>
        <?php endforeach; ?>
    </tr>

        <?php for ($i = 1; $i <= $daysInMonth; $i++) : ?>
    <tr>
        <?php 
        $currentDate = $month . '-' . str_pad($i, 2, '0', STR_PAD_LEFT); 
        $dayOfWeek = getPolishDayOfWeek($currentDate); 
        ?>
        <td><?php echo $i . '. ' . $dayOfWeek; ?></td> 
        <?php foreach ($usernames as $userId => $username) : ?>
            <td>
            <?php echo !empty($schedule[$userId][$i]) ? substr($schedule[$userId][$i], 0, 5) : '---'; ?>
            </td>
        <?php endforeach; ?>
    </tr>
<?php endfor; ?>





    </table>

</form>

<?php include 'footer.php'; ?>

</body>

<script>
function downloadTableAsImage() {
    html2canvas(document.querySelector("table")).then(canvas => {
        let image = canvas.toDataURL("image/png").replace("image/png", "image/octet-stream");
        let link = document.createElement('a');

        link.download = '<?php echo getPolishMonthName($month); ?>_grafik.png';
        link.href = image;
        link.click();
    });
}


</script>


</html>