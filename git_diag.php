<?php
exec('git config --global --add safe.directory C:/xampp/htdocs/skillBridge 2>&1');
exec('git status 2>&1', $output, $return_var);
echo "<h3>Git Status (Return Code: $return_var)</h3><pre>";
echo implode("\n", $output);
echo "</pre>";

exec('git diff --name-only --diff-filter=U 2>&1', $output2, $return_var2);
echo "<h3>Unmerged Files</h3><pre>";
echo implode("\n", $output2);
echo "</pre>";
