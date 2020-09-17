<?php

class VerificaDadosAfastamento{

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
            alert("É necessário informar a data inicial.");
            return;
        } else {
            $this->dtInicial = date_to_bd($dtInicial);
        }

        if (empty($dtFinal)) {
            alert("É necessário informar o data Finalr.");
            return;
        } else {
            $this->dtFinal = date_to_bd($dtFinal);
        }
    }

    ###########################################################

    public function verifica() {
        
        $retorno = null;
        
        /*
         *  Férias
         */
        $pessoal = new Pessoal();
        $select = "SELECT dtInicial, ADDDATE(dtInicial,numDias-1)
                 FROM tbferias
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1)))
             ORDER BY 1";

        $afast = $pessoal->select($select);
        if (!empty($afast)) {
            # Percorre os registros de $afast
            foreach ($afast as $evento) {
                $retorno[] = [$evento[0], $evento[1]];
            }
        }

        /*
         *  Licenças e Afastamentos gerais
         */
        $pessoal = new Pessoal();
        $select = "SELECT dtInicial, ADDDATE(dtInicial,numDias-1)
                 FROM tblicenca
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1)))
             ORDER BY 1";

        $afast = $pessoal->select($select);
        if (!empty($afast)) {
            # Percorre os registros de $afast
            foreach ($afast as $evento) {
                $retorno[] = [$evento[0], $evento[1]];
            }
        }

        /*
         *  Licenças prêmio
         */
        $pessoal = new Pessoal();
         $select = "SELECT dtInicial, ADDDATE(dtInicial,numDias-1)
                 FROM tblicencapremio
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1)))
             ORDER BY 1";

        $afast = $pessoal->select($select);
        if (!empty($afast)) {
            # Percorre os registros de $afast
            foreach ($afast as $evento) {
                $retorno[] = [$evento[0], $evento[1]];
            }
        }

        /*
         *  Licenças sem vencimentos
         */
        $pessoal = new Pessoal();
         $select = "SELECT dtInicial, ADDDATE(dtInicial,numDias-1)
                 FROM tblicencasemvencimentos
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1)))
             ORDER BY 1";

        $afast = $pessoal->select($select);
        if (!empty($afast)) {
            # Percorre os registros de $afast
            foreach ($afast as $evento) {
                $retorno[] = [$evento[0], $evento[1]];
            }
        }

        /*
         *  Faltas Abonadas por atestado
         */
        $pessoal = new Pessoal();
         $select = "SELECT dtInicio, ADDDATE(dtInicio,numDias-1)
                 FROM tbatestado
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicio AND ADDDATE(dtInicio,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicio AND ADDDATE(dtInicio,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicio AND '{$this->dtFinal}' >= ADDDATE(dtInicio,numDias-1)))
             ORDER BY 1";

        $afast = $pessoal->select($select);
        if (!empty($afast)) {
            # Percorre os registros de $afast
            foreach ($afast as $evento) {
                $retorno[] = [$evento[0], $evento[1]];
            }
        }

        /*
         *  Trabalho TRE
         */
        $pessoal = new Pessoal();
         $select = "SELECT data, ADDDATE(data,dias-1)
                 FROM tbtrabalhotre
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN data AND ADDDATE(data,dias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN data AND ADDDATE(data,dias-1)) 
                     OR ('{$this->dtInicial}' <= data AND '{$this->dtFinal}' >= ADDDATE(data,dias-1)))
             ORDER BY 1";

        $afast = $pessoal->select($select);
        if (!empty($afast)) {
            # Percorre os registros de $afast
            foreach ($afast as $evento) {
                $retorno[] = [$evento[0], $evento[1]];
            }
        }

        /*
         *  Folgas TRE
         */
        $pessoal = new Pessoal();
         $select = "SELECT data, ADDDATE(data,dias-1)
                 FROM tbfolga
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN data AND ADDDATE(data,dias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN data AND ADDDATE(data,dias-1)) 
                     OR ('{$this->dtInicial}' <= data AND '{$this->dtFinal}' >= ADDDATE(data,dias-1)))
             ORDER BY 1";

        $afast = $pessoal->select($select);
        if (!empty($afast)) {
            # Percorre os registros de $afast
            foreach ($afast as $evento) {
                $retorno[] = [$evento[0], $evento[1]];
            }
        }

        return $retorno;
    }

    ###########################################################
}
