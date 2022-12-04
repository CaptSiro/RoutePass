<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>JSON</title>
</head>
<body>
  <button id="submit">Submit</button>
  <script>
    document.querySelector("#submit").addEventListener("click", async _ => {
      const response = await fetch("/routing/json", {
        method: "DELETE",
        body: JSON.stringify([{
          message: "You are so stupid",
          data: {
            id: 90,
            name: "COGGERS"
          },
          array: [{
            type: "meme",
            title: "LMAO GET GOOOOD!"
          }]
        }])
      });
      
      const json = await response.json();
      console.log(json)
    });
  </script>
</body>
</html>