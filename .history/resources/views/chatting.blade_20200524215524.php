<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Global chat</title>
</head>

<body>

	<style>
		.message {
			padding: 9px;
			border-radius: 9px;
			background-color: rgb(0, 153, 255);
			display: inline-block;
			color: white;
			font-family: Segoe UI Historic, Segoe UI, Helvetica, Arial, sans-serif;
			white-space: pre;
			word-break: break-word;
		}

		.chat-box-container {}

		button {
			padding: 6px 10px;
			border: none;
			color: white;
			cursor: pointer;
			border-radius: 9px;
			background-color: rgb(0, 153, 255);
		}

		.tooltip {
			position: relative;
			display: inline-block;
			border-bottom: 1px dotted black;
		}

		.tooltip .tooltiptext {
			visibility: hidden;
			width: 120px;
			background-color: black;
			color: #fff;
			text-align: center;
			border-radius: 6px;
			padding: 5px 0;

			/* Position the tooltip */
			position: absolute;
			top: 100%;
			left: 100%;
			z-index: 1;
		}

		.tooltip:hover .tooltiptext {
			visibility: visible;
		}
	</style>

	<div class="message-container">
		@foreach ($chattings as $chatting)
		<p class="message tooltip">
			{{ $chatting->content }}
			<span class="tooltiptext">{{ $chatting->created_at }}</span>
		</p>
		@endforeach
	</div>

	<form action="/chat" method="post" class="chat-box-container">
		{{ csrf_field() }}
		<textarea id="chat-box" name="content" required></textarea>
		<button type="submit">Send ➤</button>
	</form>

</body>

</html>