<?php

class AuxilioEducacao {

    /**
     * Abriga as várias rotina do Controle do Auxíliko Educação
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ##############################################################

    public function get_dados($id = null) {

        /**
         * Informa os dados da base de dados
         * 
         * @param $id integer null O id 
         * 
         * @syntax $dependente->get_dados([$id]);
         */
        # Joga o valor informado para a variável da classe
        if (empty($id)) {
            return null;
        } else {            
            $pessoal = new Pessoal();
            return $pessoal->select("SELECT * FROM tbauxeducacao WHERE idAuxEducacao = {$id}", false);
        }
    }
    
    ##############################################################

    public function get_dadosIdDependente($idDependente = null) {

        /**
         * Informa os dados da base de dados
         * 
         * @param $id integer null O id 
         * 
         * @syntax $dependente->get_dados([$id]);
         */
        # Joga o valor informado para a variável da classe
        if (empty($idDependente)) {
            return null;
        } else {            
            $pessoal = new Pessoal();
            return $pessoal->select("SELECT * FROM tbauxeducacao WHERE idDependente = {$idDependente}");
        }
    }
    
    ##############################################################

    public function get_ultimaDataComprovada($idDependente = null) {

        /**
         * Informa os dados da base de dados
         * 
         * @param $id integer null O id 
         * 
         * @syntax $dependente->get_dados([$id]);
         */
        # Joga o valor informado para a variável da classe
        if (empty($idDependente)) {
            return null;
        } else {            
            $pessoal = new Pessoal();
            $data = $pessoal->select("SELECT dttermino FROM tbauxeducacao WHERE idDependente = {$idDependente} ORDER BY dtTermino desc LIMIT 1", false);
            
            if(empty($data[0])){
                return null;
            }else{
                return date_to_php($data[0]);
            }
        }
    }
    
    ###########################################################

    public function exibeComprovante($id) {
        /**
         * Exibe um link para exibir o pdf do certificado
         * 
         * @param $idFormacao integer null O id
         * 
         * @syntax $formacao->exibeCertificado($idFormacao);
         */
        # Monta o arquivo
        $arquivo = PASTA_COMP_AUX_EDUCA . $id . ".pdf";

        # Verifica se ele existe
        if (file_exists($arquivo)) {

            # Monta o link
            $link = new Link(null, $arquivo, "Exibe o cOMPROVANTE");
            $link->set_imagem(PASTA_FIGURAS . 'doc.png', 20, 20);
            $link->set_target("_blank");
            $link->show();
        } else {
            echo "-";
        }
    }

###########################################################
}
