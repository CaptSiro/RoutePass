<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>URL Encoded</title>
</head>
<body>
  <form action="/routing/urlencoded" method="post">
    <label>
      <input type="text" name="text">
    </label>
    <label>
      <input type="color" name="color">
    </label>
    <label>
      <input type="checkbox" name="checkbox">
    </label>
    <label>
      <input type="date" name="date">
    </label>
    <label>
      <input type="radio" name="radio">
    </label>
    <button type="submit">Submit</button>
  </form>
</body>
</html>