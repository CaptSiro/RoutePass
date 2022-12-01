<?php

  require_once __DIR__ . "/lib/routepass/src/Router.php";

  $router = new Router();
  $userRouter = new Router();
  $userRouter->get("/", [function () {
    echo "/home/user";
    exit;
  }]);
  
  $userRouter->get("/:id", [function (Request $req) {
    echo $req->param->id;
    exit;
  }]);

  
  
  $router->get("/user/id-:id~name:name", [function ($req, $res) {
    echo("Hello user: " . $req->param->id . " with name: " . $req->param->name);
    exit;
  }], ["id" => Router::REG_NUMBER, "name" => Router::REG_WORD]);
  
  $router->get("/user", [function ($req, $res) {
    echo("Welcome to user");
    exit;
  }], ["id" => Router::REG_NUMBER]);

  $router->get("/book/:bookID\\book", [function ($req, $res) {
    echo("Getting book: " . $req->param->bookID);
    exit;
  }], ["bookID" => Router::REG_NUMBER]);

  $router->get("/files/:fileName", [function ($req, $res) {
    echo("getting file: " . $req->param->fileName);
  }], ["fileName" => Router::REG_ANY]);


  $router->get("/", [function ($req, $res) {
      echo ("/home");
  }]);
  
  
  
  $router->for(["GET", "POST"],"/all/:name:id/static", [function (Request $req) {
    echo $req->param->name . ":" . $req->param->id . " hello!";
  }], ["name" => Router::REG_WORD, "id" => Router::REG_NUMBER]);



  $router->serve();




  // var_dump(json_decode(file_get_contents('php://input')));
  // var_dump($_SERVER["REQUEST_METHOD"]);
  // var_dump(getallheaders());

  // var_dump(phpversion());

  // var_dump($_SERVER);
  // var_dump(explode("/", $_SERVER["REQUEST_URI"]));

?>
<!-- <html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home</title>
</head>
<body>
  <button id="fetch">Fetch</button>
  <script>
    document.querySelector("#fetch").addEventListener("click", evt => {
      fetch("/routing/", {method: "PUT", body: JSON.stringify({user: 4, msg: "ping"})})
        .then(res => res.text().then(txt => document.body.innerHTML = txt))
    })
  </script>
</body>
</html> -->