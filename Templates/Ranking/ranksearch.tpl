<?php
if(!isset($_GET['id'])){ $_GET['id']='1'; }

// Calculate current page start for back/forward links
if (isset($rankSize)) {
    $count = $rankSize;
} elseif (isset($rankArray)) {
    $count = is_array($rankArray) ? (count($rankArray) - 1) : 0;
} else {
    $count = 0;
}

// Calculate $start (first rank on current page) from session or rank param
if (isset($_GET['rank']) && is_numeric($_GET['rank']) && $_GET['rank'] > 0) {
    $targetRank = (int)$_GET['rank'];
    $page = ceil($targetRank / 20);
    $start = ($page - 1) * 20 + 1;
} elseif (isset($_SESSION['start']) && is_numeric($_SESSION['start'])) {
    $start = (int)$_SESSION['start'];
} else {
    $start = 1;
}

if ($search == 0 && isset($_GET['rank'])) {
    $search = (int)$_GET['rank'];
}
?>
<table cellpadding="1" cellspacing="1" id="search_navi">
					<tr>						
						<td>
							<form method="post" action="statistiken.php?id=<?php echo isset($_GET['id'])? $_GET['id'] : 1; ?>">	
							<div class="search">											
								<span>rank<input type="text" class="text ra" maxlength="7" name="rank" value="<?php echo ($search == 0)? $start : $search; ?>" /></span>
								<span class="or">or</span>
								<span>name<input type="text" class="text name" maxlength="30" name="name" value="<?php if(!is_numeric($search)) {echo $search; } ?>" /></span>
                                <input type="hidden" name="ft" value="r<?php echo isset($_GET['id'])? $_GET['id'] : 1; ?>" />
								<button value="submit" name="submit" id="btn_ok" class="trav_buttons" alt="OK" /> Ok </button>
							</div>
							</form>
							<div class="navi">
<?php

if ($count > 0 || isset($rankArray) || isset($rankSize)) {
	if($count <= 20) {
		echo "&laquo; back | forward &raquo;";
	}else if($start != 1 && $start + 20 < $count) {
		echo "<a href=\"statistiken.php?id=".$_GET['id']."&amp;rank=".($start - 20)."\">&laquo; back</a> | <a href=\"statistiken.php?id=".$_GET['id']."&amp;rank=".($start + 20)."\">forward &raquo;</a>";
	}else if($start == 1 && $start + 20 < $count) {
		echo "&laquo; back | <a href=\"statistiken.php?id=".$_GET['id']."&amp;rank=".($start + 20)."\">forward &raquo;</a>";
	}else if($start != 1 && $start - 20 < $count) {
		echo "<a href=\"statistiken.php?id=".$_GET['id']."&amp;rank=".($start - 20)."\">&laquo; back</a> | forward &raquo;";
	}
}
?>


