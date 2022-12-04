<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Text</title>
</head>
<body>
  <button id="submit">Submit</button>
  <script>
    document.querySelector("#submit").addEventListener("click", async evt => {
      const response = await fetch("/routing/text", {method: "POST", body: "ping"});
      document.body.innerHTML = await response.text();
    });
  </script>
</body>
</html>