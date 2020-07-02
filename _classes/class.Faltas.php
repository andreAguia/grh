<?php

class Faltas
{
    /**
     * Abriga as várias rotina referentes as faltas do servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    ###########################################################

    /**
     * Método Construtor
     */
    public function __construct()
    {
        
    }

    ###########################################################

    function getFaltasServidor($idServidor)
    {

        # Verifica se foi informado
        if (empty($idServidor)) {
            alert("É necessário informar o id do Servidor.");
            return;
        }

        $select = "SELECT * 
                     FROM tblicenca
                    WHERE idServidor = {$idServidor}
                      AND idTpLicenca = 25 
                 ORDER BY dtInicial";
        
        $pessoal = new Pessoal();
        return $pessoal->select($select);
    }

    ###########################################################
}
