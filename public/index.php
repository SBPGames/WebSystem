<?php
	$words = [
		"pretty", "handsome", "beautiful", "amazing", "elegant", "cute",
		"gorgeous", "lovely"
	];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title>SBP Games</title>

	<link rel="icon" href="/favicon.ico" type="image/x-icon" sizes="32x32">
	<link rel="apple-touch-icon" href="/favicon.ico">
	<link rel="apple-touch-startup-image" href="/favicon.ico">

	<style>
		*{
			text-align: center;

			box-sizing: border-box;
		}

		body{
			margin-inline: 0;
			width: 100%;
			padding-inline: 1rem;
		}

		h1{
			color: #a33e18;
		}

		footer{
			position: absolute;

			bottom: 0;
			width: calc(100% - 1rem*2);
		}
	</style>
</head>
<body>
	<main>
		<hgroup>
			<h1>SBP Games</h1>
			<p><i>
				A game development studio instantiated by a team of small
				beautiful prawns!
			</i></p>
		</hgroup>

		<p>
			This page will soon be
			<strong><?= $words[random_int(0, sizeof($words) - 1)] ?></strong>!
			Follow us on Github at
			<a href="https://www.github.com/SBPGames">SBP Games organization</a>.
		</p>

		<p style="color: white;">I love you my girlfriend - Xibitol</p>
	</main>

	<footer>
		<h5>
			Copyright &copy; 2024 - SBP Games | All the code of this website
			can be found at <a href="https://www.github.com/SBPGames/WebSystem">the WebSystem git repo</a>.
		</h5>
	</footer>
</body>
</html>