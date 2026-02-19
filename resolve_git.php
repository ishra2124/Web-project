<?php
exec('git config --global --add safe.directory C:/xampp/htdocs/skillBridge 2>&1');
exec('git config --global user.email "skillbridge@dev.local" 2>&1');
exec('git config --global user.name "SkillBridge Developer" 2>&1');

// 1. Force overwrite everything with current state
echo "<h3>Finalizing Merge...</h3>";
exec('git add . 2>&1', $o, $r);
// Commit the merge (usually git expects a merge message if .git/MERGE_HEAD exists)
exec('git commit -m "Merged remote changes and applied organized local structure" 2>&1', $o2, $r2);

echo "<pre>Commit Result: " . implode("\n", $o2) . "</pre>";

// 2. Push
echo "<h3>Pushing to origin...</h3>";
exec('git push origin main 2>&1', $o3, $r3);
echo "<pre>Push Result: " . implode("\n", $o3) . "</pre>";
