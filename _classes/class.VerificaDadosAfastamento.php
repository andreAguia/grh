<?php

class VerificaDadosAfastamento {
    /*
     * Classe que verifica se o servidor $idServidor teve algum afastamento 
     * no período informado: $dtInicial e $dtFinal
     * 
     * A princípio somente usado na classe de cessao
     */

    private $idServidor;
    private $dtInicial;
    private $dtFinal;

    /**
     * Método Construtor
     */
    public function __construct($idServidor, $dtInicial, $dtFinal) {

        # Verifica se informou o $idServidor
        if (empty($idServidor)) {
            alert("É necessário informar o idServidor.");
            return;
        } else {
            $this->idServidor = $idServidor;
        }

        if (empty($dtInicial)) {
            alert("É necessário informar a data inicialc. ({$idServidor} - {$dtInicial} - {$dtFinal})");
            return;
        } else {
            $this->dtInicial = date_to_bd($dtInicial);
        }

        if (empty($dtFinal)) {
            alert("É necessário informar o data Final.");
            return;
        } else {
            $this->dtFinal = date_to_bd($dtFinal);
        }
    }

    ###########################################################

    public function verifica() {

        /*
         * Executa de fato a verificação e retorna
         */


        # Classe pessoal
        $pessoal = new Pessoal();

        /*
         *  Férias
         */

        $select = "SELECT dtInicial, ADDDATE(dtInicial,numDias-1) as dtFinal
                 FROM tbferias
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1)))
             ORDER BY 1 LIMIT 1";

        $afast = $pessoal->select($select, false);
        if (!empty($afast)) {
            return $afast;
        }
        
        /*
         *  Licenças e Afastamentos gerais
         */

        $select = "SELECT dtInicial, ADDDATE(dtInicial,numDias-1) as dtFinal
                 FROM tblicenca
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1)))
             ORDER BY 1";

        $afast = $pessoal->select($select, false);
        if (!empty($afast)) {
            return $afast;
        }

        /*
         *  Licenças prêmio
         */

        $select = "SELECT dtInicial, ADDDATE(dtInicial,numDias-1) as dtFinal
                 FROM tblicencapremio
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1)))
             ORDER BY 1";

        $afast = $pessoal->select($select, false);
        if (!empty($afast)) {
            return $afast;
        }

        /*
         *  Licenças sem vencimentos
         * 
         * 
         * 
         * 
         * 
         *  O Problema está abaixo !!!!!!!!!
         */

        $select = "SELECT dtInicial, IFNULL(tblicencasemvencimentos.dtretorno, ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1)) as dtFinal
                 FROM tblicencasemvencimentos
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicial AND IFNULL(tblicencasemvencimentos.dtretorno, ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1))) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicial AND IFNULL(tblicencasemvencimentos.dtretorno, ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1))) 
                     OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= IFNULL(tblicencasemvencimentos.dtretorno, ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1))))
             ORDER BY 1";
        
        $afast = $pessoal->select($select, false);

        #var_dump($afast);

        if (!empty($afast)) {
            return $afast;
        }

        /*
         *  Faltas Abonadas por atestado
         */

        $select = "SELECT dtInicio as dtInicial, ADDDATE(dtInicio,numDias-1) as dtFinal
                 FROM tbatestado
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicio AND ADDDATE(dtInicio,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicio AND ADDDATE(dtInicio,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicio AND '{$this->dtFinal}' >= ADDDATE(dtInicio,numDias-1)))
             ORDER BY 1";

        $afast = $pessoal->select($select, false);
        if (!empty($afast)) {
            return $afast;
        }

        /*
         *  Trabalho TRE
         */

        $select = "SELECT data as dtInicial, ADDDATE(data,dias-1) as dtFinal
                 FROM tbtrabalhotre
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN data AND ADDDATE(data,dias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN data AND ADDDATE(data,dias-1)) 
                     OR ('{$this->dtInicial}' <= data AND '{$this->dtFinal}' >= ADDDATE(data,dias-1)))
             ORDER BY 1";

        $afast = $pessoal->select($select, false);
        if (!empty($afast)) {
            return $afast;
        }

        /*
         *  Folgas TRE
         */

        $select = "SELECT data as dtInicial, ADDDATE(data,dias-1) as dtFinal
                 FROM tbfolga
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN data AND ADDDATE(data,dias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN data AND ADDDATE(data,dias-1)) 
                     OR ('{$this->dtInicial}' <= data AND '{$this->dtFinal}' >= ADDDATE(data,dias-1)))
             ORDER BY 1";

        $afast = $pessoal->select($select, false);

        if (!empty($afast)) {
            return $afast;
        }
    }

    ###########################################################
}
