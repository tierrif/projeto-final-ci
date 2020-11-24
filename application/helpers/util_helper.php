<? defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Verifica se a chave $key de $array
 * existe e retorna o valor. Se não existe,
 * retorna null.
 */
function arrayValue($array, $key) {
    // Ambos os parâmetros são necessários.
    if ($array === null || $key === null) return null;
    // Verifica se a chave existe no array.
    if (isset($array[$key]) && !empty($array[$key])) {
        // Retorna o valor da chave.
        return $array[$key];
    }

    // Retorna nulo por padrão.
    return null;
}
