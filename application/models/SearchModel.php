<? defined('BASEPATH') or exit('No direct script access allowed');

class SearchModel extends CI_Model {
    /*
     * Pesquisar conteúdo de uma tabela e
     * coluna.
     *
     * $table - Tabela onde pesquisar.
     * $column - Coluna da tabela onde pesquisar. Opcional: Pesquisa em todas se nulo.
     * $content - Conteúdo a pesquisar. Opcional: Retorna todos os resultados.
     * $ignoreCase - Ignorar maiúsculas/minúsculas. Opcional: Ignora por defeito.
     */
    public function searchByTableAndColumn($table, $column = null, $content = null, $ignoreCase = true) {
        if ($ignoreCase) {
            $column = "LOWER($column)";
            $content = strtolower($content);
        }
        $this->db->where($column, $content);
        return $this->db->get($table)->result_array();
    }
}