<?php
  //todo finish Result::all()

  class ResultSet {
    private $success, $failures;
    public function getSuccess () {
      return $this->success;
    }
    public function getFailures () {
      return $this->failures;
    }

    public function __construct (?array $success, ?array $failures) {
      $this->success = empty($success) ? null : $success;
      $this->failures = empty($failures) ? null : $failures;
    }


    public function isSuccess (): bool {
      return isset($this->succ);
    }

    public function isFailure (): bool {
      return isset($this->failure);
    }

    public function forwardFailure (Response $res) {
      if (isset($this->failure)) {
        $res->json($this->failure);
      }
    }

    public function strip (Closure $failFN) {
      if ($this->isFailure()) {
        return $failFN($this->failures);
      }

      return $this->succ;
    }
  }

  class Result {
    private $succ, $failure;
    public function getSuccess () {
      return $this->succ;
    }
    public function getFailure () {
      return $this->failure;
    }

    public function __construct ($success, ?Exc $failure) {
      $this->succ = $success;
      $this->failure = $failure;
    }


    public function isSuccess (): bool {
      return isset($this->succ);
    }

    public function isFailure (): bool {
      return isset($this->failure);
    }



    public function succeeded (Closure $fn): Result {
      if ($this->isSuccess()) {
        return success($fn($this->succ));
      }

      return fail($this->failure);
    }

    public function failed (Closure $fn): Result {
      if ($this->isFailure()) {
        return fail($fn($this->failure));
      }

      return success($this->succ);
    }

    public function forwardFailure (Response $res) {
      if (isset($this->failure)) {
        $res->json($this->failure);
      }
    }

    public function either (Closure $successFN, Closure $failFN): Result {
      if ($this->isSuccess()) {
        return success($successFN($this->succ));
      }

      return fail($failFN($this->failure));
    }

    public function strip (Closure $failFN) {
      if ($this->isFailure()) {
        return $failFN();
      }

      return $this->succ;
    }

    public static function all (...$results): ResultSet {
      if (empty($results)) return fail(new NullPointerExc("Working with 0 results. You must pass at least one."));

      $failed = [];
      $succeeded = [];

      foreach ($results as $res) {
        if ($res->isFailure()) {
          $failed[] = $res->getFailure();
        } else {
          $succeeded[] = $res->getSuccess();
        }
      }

      return new ResultSet($succeeded, $failed);
    }
  }



  function success ($value): Result {
    return new Result($value, null);
  }



  function fail (Exc $exception): Result {
    return new Result(null, $exception);
  }