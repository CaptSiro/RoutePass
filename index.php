<?php
  
  require_once __DIR__ . "/lib/dotenv/dotenv.php";
  /**
   * @type HomeRouter $router
   */
  $router = require __DIR__ . "/lib/routepass/routepass.php";
  $router->setViewDirectory(__DIR__ . "/views");
  $router->onErrorEvent(function (string $message, Request $request, Response $response) {
    $response->render("error", ["message" => $message]);
  });
  $router->setBodyParser(HomeRouter::BODY_PARSER_TEXT());

  
  
  $userRouter = new Router();
  $userRouter->get("/", [function (Request $req) {
    echo "/home/" . $req->param->get("name") . $req->param->get("id")[0];
    exit;
  }]);
  
  $userRouter->get("/:id[]", [function (Request $req) {
    var_dump($req->param->get("id"));
    exit;
  }], ["id" => Router::REG_NUMBER]);
  
  $userRouter->get("/static", [function () {
    echo "/home/user/static";
  }]);
  
  
  
  
  
  $router->get("/book/:bookID\\book", [function ($req, $res) {
    echo("Getting book: " . $req->param->get("bookID"));
    exit;
  }], ["bookID" => Router::REG_NUMBER]);

  $router->get("/files/:fileName", [function ($req, $res) {
    echo("getting file: " . $req->param->get("fileName"));
  }], ["fileName" => Router::REG_ANY]);
  
  $router->get("/", [function (Request $request, Response $response) {
    $response->render("index", ["name" => "John"]);
  }]);
  
  $router->for(["GET", "POST"],"/for/:name:id/static", [function (Request $req) {
    echo $req->param->get("name") . ":" . $req->param->get("id") . " hello! (for)";
  }], ["name" => Router::REG_WORD, "id" => Router::REG_NUMBER]);
  
  $router->forAll("/all/:name:id/static", [function (Request $req) {
    echo $req->param->get("name") . ":" . $req->param->get("id") . " hello! (all)";
  }], ["name" => Router::REG_WORD, "id" => Router::REG_NUMBER]);
  
  

  $router->use("/:name-:id[]", $userRouter, ["id" => Router::REG_NUMBER]);
  
  
  
  
  
  $userDomainRouter = new Router();
  
  $userDomainRouter->get("/", [function (Request $req) {
    echo "domain: " . $req->domain->get("user");
  }]);
  
  $userDomainRouter->get(
    "/:post\\_:page",
    [function (Request $req) {
      echo "<strong>" . $req->domain->get("user") . "</strong> getting post: <strong>" . $req->param->get("post") . "</strong> page(" . $req->param->get("page") . ")";
    }],
    [
      "post" => Router::REG_SENTENCE_LOWER,
      "page" => Router::REG_NUMBER
    ]
  );
  
  $env = new Env(__DIR__ . "/.env");
  $router->domain("[user].$env->HOST", $userDomainRouter, ["user" => Router::REG_WORD_LOWER]);
  
  
  
  
  
  $staticRouter = new Router();
  $staticRouter->get("/", [function (Request $request) {
    echo "static domain router" . $request->query->get("name");
  }]);
  $router->domain("static.$env->HOST", $staticRouter);
  
  
  
  
  
  
  
  
  $bodyParserRouter = new Router();
  $bodyParserRouter->get("/file-upload", [function (Request $request, Response $response) {
    $response->render("file-upload");
  }]);
  $bodyParserRouter->post("/file-upload", [function (Request $request, Response $response) {
    $response->json($request->body->getMap());
  }]);
  $bodyParserRouter->get("/urlencoded", [function (Request $request, Response $response) {
    $response->render("urlencoded");
  }]);
  $bodyParserRouter->post("/urlencoded", [function (Request $request, Response $response) {
    $response->json($request->body->getMap());
  }]);
  $bodyParserRouter->get("/json", [function (Request $request, Response $response) {
    $response->render("json");
  }]);
  $bodyParserRouter->delete("/json", [function (Request $request, Response $response) {
    $response->json($request->body->getMap());
  }]);
  $bodyParserRouter->get("/text", [function (Request $request, Response $response) {
    $response->render("text");
  }]);
  $bodyParserRouter->post("/text", [function (Request $request, Response $response) {
    $response->json($request->body->getMap());
  }]);
  $router->domain("body.localhost", $bodyParserRouter);
  
  

  
  
  $router->serve();

  // var_dump(json_decode(file_get_contents('php://input')));
  // var_dump($_SERVER["REQUEST_METHOD"]);
  // var_dump(getallheaders());

  // var_dump(phpversion());

  // var_dump($_SERVER);
  // var_dump(explode("/", $_SERVER["REQUEST_URI"]));

?>