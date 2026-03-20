<?php

class Candidato {
    ##############################################################

    public function get_dados($id = null) {

        /**
         * Informa os dados da base de dados
         * 
         * @param $id integer null O id 
         * 
         * @syntax Candidato->get_dados([$id]);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (empty($id)) {
            return null;
        } else {

            # Pega os dados
            $select = "SELECT * 
                     FROM tbcandidato
                    WHERE idCandidato = {$id}";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);

            # Retorno
            return $row;
        }
    }

###########################################################

    function apagaTabelaCsv() {

        # Apaga a tabela 
        $select = 'SELECT idCandidatoImporta FROM tbcandidatoimporta';

        $pessoal = new Pessoal();
        $row = $pessoal->select($select);

        $pessoal->set_tabela("tbcandidatoimporta");
        $pessoal->set_idCampo("idCandidatoImporta");

        foreach ($row as $tt) {
            $pessoal->excluir($tt[0]);
        }
    }

    ###########################################################

    /*
     * Retorna o número de registros da tabela temporária do upload
     */

    function get_numRegistrosTabelaUpload() {


        $select = "SELECT idCandidatoImporta FROM tbcandidatoimporta";

        $pessoal = new Pessoal();
        return $pessoal->count($select);
    }

    ###########################################################

    /*
     * Retorna o número de registros da tabela temporária do upload
     */

    function get_numRegistrosTabelaUploadComErro() {


        $select = "SELECT idCandidatoImporta FROM tbcandidatoimporta WHERE erro IS NOT NULL";

        $pessoal = new Pessoal();
        return $pessoal->count($select);
    }

    ###########################################################

    public function exibeCotas($id = null) {
        /**
         * Exibe as cotas desse candidato para uso na tabela
         * 
         * @syntax Candidato->exibeCotas($id);
         */
                
        if (empty($id)) {
            return null;
        } else {
            # Pega os Dados
            $dados = $this->get_dados($id);
            
            # Inicia as variáveis
            $return = null;
            $marcador = false;
            
            # PCD
            if(!empty($dados["classifPcd"])){
                $return .= "PCD";
                $marcador = true;
            }
            
            # Negros e Índios
            if(!empty($dados["classifNi"])){
                if($marcador){
                    $return .= "<br/>";
                }
                $return .= "Negros e Índios";
            }
            
            # Hipossuficiente Econômic
            if(!empty($dados["classifHipo"])){
                if($marcador){
                   $return .= "<br/>";
                }
                $return .= "Hipossuficiente Econômic";
            }
            
            return $return;
        }
            
    }    

    ###########################################################
}
