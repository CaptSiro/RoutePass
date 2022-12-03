<?php
  
  require_once __DIR__ . "/lib/dotenv/dotenv.php";
  /**
   * @type HomeRouter $router
   */
  $router = require __DIR__ . "/lib/routepass/routepass.php";

  
  
  $userRouter = new Router();
  $userRouter->get("/", [function (Request $req) {
    echo "/home/" . $req->param->name . $req->param->id[0];
    exit;
  }]);
  
  $userRouter->get("/:id[]", [function (Request $req) {
    var_dump($req->param);
    exit;
  }], ["id" => Router::REG_NUMBER]);
  
  $userRouter->get("/static", [function () {
    echo "/home/user/static";
  }]);
  
  
  
  
  
  $router->get("/book/:bookID\\book", [function ($req, $res) {
    echo("Getting book: " . $req->param->bookID);
    exit;
  }], ["bookID" => Router::REG_NUMBER]);

  $router->get("/files/:fileName", [function ($req, $res) {
    echo("getting file: " . $req->param->fileName);
  }], ["fileName" => Router::REG_ANY]);
  
  $router->get("/", [function () {
      echo "<h1>Landing page.</h1>";
  }]);
  
  $router->for(["GET", "POST"],"/for/:name:id/static", [function (Request $req) {
    echo $req->param->name . ":" . $req->param->id . " hello! (for)";
  }], ["name" => Router::REG_WORD, "id" => Router::REG_NUMBER]);
  
  $router->forAll("/all/:name:id/static", [function (Request $req) {
    echo $req->param->name . ":" . $req->param->id . " hello! (all)";
  }], ["name" => Router::REG_WORD, "id" => Router::REG_NUMBER]);
  
  

  $router->use("/:name-:id[]", $userRouter, ["id" => Router::REG_NUMBER]);
  
  
  
  
  
  $userDomainRouter = new Router();
  
  $userDomainRouter->get("/", [function (Request $req) {
    echo "domain: " . $req->domain->user;
  }]);
  
  $userDomainRouter->get(
    "/:post\\_:page",
    [function (Request $req) {
      echo "<strong>" . $req->domain->user . "</strong> getting post: <strong>" . $req->param->post . "</strong> page(" . $req->param->page . ")";
    }],
    [
      "post" => Router::REG_SENTENCE_LOWER,
      "page" => Router::REG_NUMBER
    ]
  );
  
  $env = new Env(__DIR__ . "/.env");
  $router->domain("[user].$env->HOST", $userDomainRouter, ["user" => Router::REG_WORD_LOWER]);
  
  
  
  
  
  $staticRouter = new Router();
  $staticRouter->get("/", [function () {
    echo "static domain router";
  }]);
  $router->domain("static.$env->HOST", $staticRouter);
  
  

  
  
  $router->serve();

  // var_dump(json_decode(file_get_contents('php://input')));
  // var_dump($_SERVER["REQUEST_METHOD"]);
  // var_dump(getallheaders());

  // var_dump(phpversion());

  // var_dump($_SERVER);
  // var_dump(explode("/", $_SERVER["REQUEST_URI"]));

?>