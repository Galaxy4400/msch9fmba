<main class="page">
	<? 
		if ($is_index) {
			require('main_page.php');
		} else {
			require('inner_page.php');
		}
	?>
</main>