<?php

class Licenca
{

    /**
     * Abriga as várias rotina referentes a cessão de servidor da Uenf para outro órgão
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
##############################################################

    public function exibeNome($idTpLicenca = null)
    {
        # Verifica se o id foi informado
        if (vazio($idTpLicenca)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $servidor = new Pessoal();
        $select = "SELECT nome, lei
                     FROM tbtipolicenca
                    WHERE idTpLicenca = {$idTpLicenca}";
                    
        $licenca = $servidor->select($select, false);
        p($licenca[0],"pTipoLicenca");
        p($licenca[1],"pLeiLicenca");
    }

##############################################################
}
