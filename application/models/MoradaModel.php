<? defined('BASEPATH') or exit('No direct script access allowed');

class MoradaModel extends MY_Model {
    public function __construct() {
        parent::__construct();
    }

    public function getTable() {
        return 'morada';
    }
}