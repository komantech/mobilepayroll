<html>
<head><title>Test Form</title></head>
<body>
<?php
include('AppClass.php');

getallemployees();
?>

<form method='POST' action='clockuser.php'>
<input name='totable_id' type='text' value='id'/>
<input name='totable_license' type='text' value='license'/>
<input name='totable_date' type='text' value='date'/>
<input type='submit' value='submit' />
</form>
</body>
</html>
