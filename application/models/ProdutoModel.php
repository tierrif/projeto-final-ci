<? defined('BASEPATH') or exit('No direct script access allowed');

class ProdutoModel extends MY_Model {
    public function getAllWithDetails($limit, $start) {
        $all = $this->getAll($limit, $start);
        foreach ($all as &$one) $one['detalhes_uri'] = base_url('ProdutosAdmin/details/' . $one['id']);
        return $all;
    }

    public function getTable() {
        return 'produto';
    }
}