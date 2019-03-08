<?php
/*
 *  Copyright (c) 2014-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

final class BuildChecker {
  private static function MakeCheckedValue(array $foo) {
    assert(array_key_exists('OK', $foo), 'invalid JSON data');
    assert(array_key_exists('Value', $foo), 'invalid JSON data');
    assert(array_key_exists('Required Value', $foo), 'invalid JSON data');
    assert(
      (is_bool($foo['OK'])),
      'invalid JSON data'
    );
    // UNSAFE
    return $foo;
  }

  public static function Check(
    PerfOptions $options,
    string $build,
    array $data,
    array $skipKeys
  ): void {
    $failed = 0;
    foreach ($data as $k => $v) {
      if (in_array($k, $skipKeys)) {
        continue;
      }
      assert(is_array($v), '%s is not an array', $k);
      $v = self::MakeCheckedValue($v);
      if ($v['OK']) {
        continue;
      }
      ++$failed;
      fprintf(
        STDERR,
        "%s is not suitable for benchmarking:\n".
        "  %s: %s\n".
        "  Required: %s\n",
        $build,
        $k,
        var_export($v['Value'], true),
        var_export($v['Required Value'], true)
      );
    }
    if ($failed === 0 || $options->notBenchmarking) {
      return;
    }
    fwrite(
      STDERR,
      "Exiting due to invalid config. You can run anyway with ".
      "--i-am-not-benchmarking, but the results will not be suitable for ".
      "any kind of comparison.\n"
    );
    exit(1);
  }
}
