<?php 
// permissoes de escrita sobre esta pasta
$config['upload_path'] = './assets/receitas/';
// tem premissoes para a raiz do projeto
$config['allowed_types'] = 'jpg|png|pdf';// mime
$config['max_size'] = '8192';// kb 8192 <=> 8mb
$config['file_name'] = md5(time()); // time() em md5.
?>