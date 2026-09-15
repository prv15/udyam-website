<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$definition = (require dirname(__DIR__) . '/app/Config/modules.php')['tenders'];
$reflection = new ReflectionClass(App\Controllers\Admin\ModulesController::class);
$validate = $reflection->getMethod('validatedData');
foreach (['tender', 'notice', 'corrigendum', 'Expression of Interest', '  Custom Type  ', '', str_repeat('x', 61)] as $type) {
    $_POST = ['type' => $type, 'region' => 'Test', 'invited_by' => 'Test', 'title' => 'Test', 'submission_mode' => 'Online'];
    $controller = $reflection->newInstanceWithoutConstructor();
    $reflection->getProperty('request')->setValue($controller, new App\Core\Request());
    [$data, $errors] = $validate->invoke($controller, $definition);
    $invalid = trim($type) === '' || mb_strlen(trim($type)) > 60;
    if (isset($errors['type']) !== $invalid || $data['type'] !== trim($type)) throw new Exception('Type validation failed');
}
$_POST['type'] = 'Custom';
$_POST['submission_mode'] = 'Invalid';
$controller = $reflection->newInstanceWithoutConstructor();
$reflection->getProperty('request')->setValue($controller, new App\Core\Request());
[, $errors] = $validate->invoke($controller, $definition);
if (!isset($errors['submission_mode'])) throw new Exception('Other select validation bypassed');
$field = $definition['fields']['type']; $name = 'type'; $values = ['type' => '<Custom & Type>'];
ob_start(); require dirname(__DIR__) . '/app/Views/admin/modules/custom-record-type.php'; $html = ob_get_clean();
if (!str_contains($html, 'value="&lt;Custom &amp; Type&gt;" selected')) throw new Exception('Edit escaping/preservation failed');
echo "PASS: default/custom validation, trim, required, length, other selects, edit preservation and escaping\n";
