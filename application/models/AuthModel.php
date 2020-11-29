<? defined('BASEPATH') or exit('No direct script access allowed');

class AuthModel extends MY_Model {
    public function getAllPermissions() {
        return [
            'admin',
            'manage-accounts',
            'manage-entities',
            'create-consultas',
            'customer-support',
            'manage-products'
        ];
    }

    public function getAllWithDetails() {
        $all = $this->getAll();
        foreach ($all as &$one) {
            $one['detalhes_uri'] = base_url('ContasAdmin/details/' . $one['id']);
            $one['permissions'] = implode(',', unserialize($one['permissions']));
        }
        return $all;
    }

    public function getByIdWithDetails($id) {
        $one = $this->getById($id);
        // $one['permissions'] = implode(',', unserialize($one['permissions']));
        return $one;
    }

    public function checkPassword($usr, $pwd) {
        print_r(hash('sha256', $pwd));
        return $this->getByUsername($usr)['password'] === hash('sha256', $pwd);
    }

    public function getByUsername($usr) {
        return $this->db->get_where($this->getTable(), ['username' => $usr])->row_array();
    }

    public function isLoggedIn() {
        if (!$this->session->userdata('token')) return false;
        return $this->getBySession($this->session->userdata('token')) != null;
    }

    public function createSession($usr) {
        $this->db->where('username', $usr);
        $sess = hash('sha256', time());
        $this->db->update($this->getTable(), ['session' => $sess]);
        $this->session->set_userdata('token', $sess);
    }

    public function getBySession($sess) {
        return $this->db->get_where($this->getTable(), ['session' => $sess])->row_array();
    }

    public function hasPermission($permission) {
        $conta = $this->getBySession($this->session->userdata('token'));
        if (!$conta) return false;
        $perms = unserialize($conta['permissions']);
        return in_array($permission, $perms) || in_array('admin', $perms); // admin tem permissões totais.
    }

    public function getTable() {
        return 'conta';
    }
}