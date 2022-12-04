<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>File upload</title>
</head>
<body>
  <input type="file">
  <button id="upload-button">Upload</button>

  <script>
    const fileInput = document.querySelector("input[type=file]");
    const button = document.querySelector("#upload-button");
    button.addEventListener("click", async evt => {
      const formData = new FormData();
      formData.set("file", fileInput.files[0]);
      formData.set("name", "GYZE");
      formData.set("pepeW", "Did you say The Deceit?!");
      
      const response = await fetch("/routing/file-upload", {
        body: formData,
        method: "POST",
      });
      
      const json = await response.text();
      document.body.innerHTML = json;
    });
  </script>
</body>
</html>