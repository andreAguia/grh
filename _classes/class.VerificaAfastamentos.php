<?php

class VerificaAfastamentos {

    private $idServidor;
    private $dtInicial;
    private $dtFinal;
    private $tabela;
    private $id;

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

    public function setIsento($tabela, $id) {
        $this->tabela = $tabela;
        $this->id = $id;
    }

    ###########################################################

    public function verifica() {
        
        /*
         * Inicia a variável de retorno
         */
        $retorno = null;        
        
        /*
         *  Férias
         */
        $pessoal = new Pessoal();
        $select = "SELECT idFerias
                 FROM tbferias
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1)))
        ";

        $afast = $pessoal->select($select);
        if (!empty($afast)) {
            # Percorre os registros de $afast
            foreach ($afast as $evento) {
                # Verifica se tem registro Isento
                if ($this->tabela == "tbferias") {
                    if ($this->id <> $evento[0]) {
                        $retorno = "Férias";
                    }
                } else {
                    $retorno = "Férias";
                }
            }
        }

        /*
         *  Licenças e Afastamentos gerais
         */
        $pessoal = new Pessoal();
        $select = "SELECT idLicenca, idTpLicenca
                 FROM tblicenca
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1)))
        ";

        $afast = $pessoal->select($select);
        if (!empty($afast)) {
            # Percorre os registros de $afast
            foreach ($afast as $evento) {
                # Verifica se tem registro Isento
                if ($this->tabela == "tblicenca") {
                    if ($this->id <> $evento[0]) {
                        $retorno = $pessoal->get_nomeTipoLicenca($evento[1]);
                    }
                } else {
                    $retorno = $pessoal->get_nomeTipoLicenca($evento[1]);
                }
            }
        }

        /*
         *  Licenças prêmio
         */
        $pessoal = new Pessoal();
        $select = "SELECT idLicencaPremio
                 FROM tblicencapremio
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1)))
        ";

        $afast = $pessoal->select($select);
        if (!empty($afast)) {
            # Percorre os registros de $afast
            foreach ($afast as $evento) {
                # Verifica se tem registro Isento
                if ($this->tabela == "tblicencapremio") {
                    if ($this->id <> $evento[0]) {
                        $retorno = "Licença Prêmio";
                    }
                } else {
                    $retorno = "Licença Prêmio";
                }
            }
        }

        /*
         *  Licenças sem vencimentos
         */
        $pessoal = new Pessoal();
        $select = "SELECT idLicencaSemVencimentos
                 FROM tblicencasemvencimentos
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1)))
        ";

        $afast = $pessoal->select($select);
        if (!empty($afast)) {
            # Percorre os registros de $afast
            foreach ($afast as $evento) {
                # Verifica se tem registro Isento
                if ($this->tabela == "tblicencasemvencimentos") {
                    if ($this->id <> $evento[0]) {
                        $retorno = "Licença Sem Vencimentos";
                    }
                } else {
                    $retorno = "Licença Sem Vencimentos";
                }
            }
        }

        /*
         *  Faltas Abonadas por atestado
         */
        $pessoal = new Pessoal();
        $select = "SELECT idAtestado
                 FROM tbatestado
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicio AND ADDDATE(dtInicio,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicio AND ADDDATE(dtInicio,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicio AND '{$this->dtFinal}' >= ADDDATE(dtInicio,numDias-1)))
        ";

        $afast = $pessoal->select($select);
        if (!empty($afast)) {
            # Percorre os registros de $afast
            foreach ($afast as $evento) {
                # Verifica se tem registro Isento
                if ($this->tabela == "tbatestado") {
                    if ($this->id <> $evento[0]) {
                        $retorno = "Falta Abonada";
                    }
                } else {
                    $retorno = "Falta Abonada";
                }
            }
        }

        /*
         *  Trabalho TRE
         */
        $pessoal = new Pessoal();
        $select = "SELECT idTrabalhoTre
                 FROM tbtrabalhotre
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN data AND ADDDATE(data,dias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN data AND ADDDATE(data,dias-1)) 
                     OR ('{$this->dtInicial}' <= data AND '{$this->dtFinal}' >= ADDDATE(data,dias-1)))
        ";

        $afast = $pessoal->select($select);
        if (!empty($afast)) {
            # Percorre os registros de $afast
            foreach ($afast as $evento) {
                # Verifica se tem registro Isento
                if ($this->tabela == "tbtrabalhotre") {
                    if ($this->id <> $evento[0]) {
                        $retorno = "Trabalho no TRE";
                    }
                } else {
                    $retorno = "Trabalho no TRE";
                }
            }
        }

        /*
         *  Folgas TRE
         */
        $pessoal = new Pessoal();
        $select = "SELECT idFolga
                 FROM tbfolga
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN data AND ADDDATE(data,dias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN data AND ADDDATE(data,dias-1)) 
                     OR ('{$this->dtInicial}' <= data AND '{$this->dtFinal}' >= ADDDATE(data,dias-1)))
        ";

        $afast = $pessoal->select($select);
        if (!empty($afast)) {
            # Percorre os registros de $afast
            foreach ($afast as $evento) {
                # Verifica se tem registro Isento
                if ($this->tabela == "tbfolga") {
                    if ($this->id <> $evento[0]) {
                        $retorno = "Folga pelo TRE";
                    }
                } else {
                    $retorno = "Folga pelo TRE";
                }
            }
        }

        return $retorno;
    }

    ###########################################################
}
