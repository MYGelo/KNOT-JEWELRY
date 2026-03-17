<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta
		name="viewport"
		content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
	>
	<meta
		http-equiv="X-UA-Compatible"
		content="ie=edge"
	>
	<title>Coming Soon</title>
</head>

<style>
	* {
		box-sizing: border-box;
		margin: 0;
		padding: 0;
		font-family: sans-serif;
	}

	.coming-soon {
		display: flex;
		flex-direction: column;
		width: 100vw;
		height: 100vh;
		background-color: #CFDBC3;
	}

	.header {
		margin-top: 40px;
		margin-left: 40px;
		width: 300px;

		img {
			width: 100%;
		}
	}

	.body {
		padding: 0 16px;
		flex-grow: 1;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		gap: 16px;
		text-align: center;
		color: #fff;
		font-size: 18px;

		h1 {
			font-size: 48px;
		}
	}

	@media (max-width: 767px) {
		.header {
			margin: 40px auto;
		}
	}
</style>

<body>

<main class="coming-soon">
	<?php if (!empty(get_field('header_logo', 'option'))): ?>
		<div class="header">
			<img
				src="<?= get_field('header_logo', 'option')['url'] ?>"
				alt="<?= get_field('header_logo', 'option')['alt'] ?>"
			>
		</div>
	<?php endif; ?>

	<div class="body">
		<h1>Sorry, website is on maintenance.</h1>
		<p>We will return as fast as possible.</p>
	</div>
</main>

</body>
</html>