<?php

class Candidato {
    
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
}
