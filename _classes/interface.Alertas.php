<?php

class Alertas {

    /**
     * Classe Alertas encapsula as rotinas dos alertas
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    private $itens = array(
        array('Aposentadoria', 'aposentadoria'),
        array('Auxílio Creche', 'creche'),
        array('Benefícios', 'beneficios'),
        array('Cadastro Geral', 'cadastro'),
        array('Cargo em Comissão', 'comissao'),
        array('Concurso', 'concurso'),
        array('Cedidos', 'cedidos'),
        array('Férias', 'ferias'),
        array('Licenças', 'licencas'),
        array('Motorista', 'motorista'),
        array('Perfil', 'perfil'),
        array('Progressão', 'progressao'),
        array('TRE', 'tre'),
        array('Triênio', 'trienio'),
    );

    ######################################################################################################################    

    public function menu($fase) {

//        # Ordena as categorias
//        function cmp($a, $b) {
//            # Função específica que compara se $a é maior que $b
//            return $a[0] > $b[0];
//        }
//
//        // Ordena
//        usort($this->itens, 'cmp');
//        
//        Estava dando deprecated na função usort
        

        tituloTable('Categorias');
        br();
        
        $menu = new Menu();       

        foreach ($this->itens as $ii) {
            if ($fase == $ii[1]) {
                $menu->add_item('link', '<b>| ' . $ii[0] . ' |</b>', '?fase=menu&categoria=' . $ii[1]);
            } else {
                $menu->add_item('link', $ii[0], '?fase=menu&categoria=' . $ii[1]);
            }
        }

        #$menu->add_item('link','Temporal','?fase=temporalCargo');  # Retirado por imprecisão
        $menu->show();
    }

    ######################################################################################################################

    public function getNomeCategoria($categoria) {

        # Verifica se a categoria foi preenchida
        if (empty($categoria)) {
            return null;
        }

        foreach ($this->itens as $ii) {
            if ($categoria == $ii[1]) {
                return $ii[0];
            }
        }
    }

}
