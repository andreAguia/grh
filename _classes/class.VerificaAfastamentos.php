<?php

class VerificaAfastamentos {
    /*
     * Classe qie informa o afastamento de um servidor específico
     */

    private $idServidor;

    /*
     * do período a ser procurado
     */
    private $dtInicial;
    private $dtFinal;

    /*
     * do tipo
     */
    private $tipoLicenca;

    /*
     * da isenção
     */
    private $tabela;
    private $id;

    /*
     * do retorno
     */
    private $afastamento = false;
    private $detalhe = null;
    private $periodo = null;
    private $tipo = null;

    ###########################################################

    /**
     * Método Construtor
     * Aproveita para informar o id do servidor
     */
    public function __construct($idServidor) {
        # Verifica se informou o $idServidor
        if (empty($idServidor)) {
            alert("É necessário informar o idServidor.");
            return;
        } else {
            $this->idServidor = $idServidor;
        }
    }

    ##########################################################

    /*
     * Informa o período a ser procurado se o servidor tem afastamantos
     * Se não for informado será considerada a data de hoje
     */

    function setPeriodo($dtInicial, $dtFinal = null) {
        if (empty($dtInicial)) {
            alert("É necessário informar a data inicial.");
            return;
        } else {
            $this->dtInicial = date_to_bd($dtInicial);
        }

        if (empty($dtFinal)) {
            # Quando somente data Inicial é fornecida é para saber se o
            # servidor está afastado naquela data
            $this->dtFinal = $this->dtInicial;
        } else {
            $this->dtFinal = date_to_bd($dtFinal);
        }
    }

    ###########################################################
    /*
     * Se tem que retirar ou isentar algum registro e tabela da busca
     * Usado em rotinas de cadastro de afastamento para não achar a si mesma
     */

    public function setIsento($tabela, $id) {
        $this->tabela = $tabela;
        $this->id = $id;
    }

    ###########################################################
    /*
     * Informa o Tipo de Licença incluído
     */

    public function setTipoLicenca($tipoLicenca) {
        $this->tipoLicenca = $tipoLicenca;
    }

    ###########################################################
    /*
     * Pega os valores de retorno
     */

    function getAfastamento() {
        return $this->afastamento;
    }

    function getDetalhe() {
        return $this->detalhe;
    }

    function getPeriodo() {
        return $this->periodo;
    }

    function getTipo() {
        return $this->tipo;
    }

    ###########################################################

    public function verifica() {

        /*
         * Inicia a variável de retorno
         */
        $retorno = null;

        /*
         * Conecta com o banco
         */
        $pessoal = new Pessoal();

        # Altera para hoje quando o período está em branco
        if (empty($this->dtInicial)) {
            $this->dtInicial = date("Y-m-d");
            $this->dtFinal = date("Y-m-d");
        }

        #################

        /*
         *  Férias
         */
        $select = "SELECT idFerias, 
                          anoExercicio,
                          dtInicial,
                          numDias,
                          ADDDATE(dtInicial,numDias-1) as dtFinal
                     FROM tbferias
                    WHERE idServidor = {$this->idServidor}
                      AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                       OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                       OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1)))";

        // se tiver isenção
        if ($this->tabela == "tbferias" AND !empty($this->id)) {
            $select .= " AND idFerias <> {$this->id}";
        }

        $select .= " ORDER BY dtInicial";

        $afast = $pessoal->select($select, false);

        if (!empty($afast)) {
            $this->afastamento = "Férias";
            $this->detalhe = "Exercício {$afast['anoExercicio']}";
            $this->periodo = date_to_php($afast['dtInicial']) . " a " . date_to_php($afast['dtFinal']) . " - " . $afast['numDias'] . " dias";
            $this->tipo = "Férias";
            return true;
        }

        #################

        /*
         *  Licenças e Afastamentos gerais
         */
        $select = "SELECT idLicenca, 
                          tbtipolicenca.nome,
                          dtInicial,
                          numDias,
                          ADDDATE(dtInicial,numDias-1) as dtFinal,
                          tblicenca.idTpLicenca as tipo
                 FROM tblicenca JOIN tbtipolicenca USING (idTpLicenca)
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                   OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                   OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1))
                   OR (dtInicial <= '{$this->dtFinal}' AND numDias IS NULL))";

        // se tiver isenção
        if ($this->tabela == "tblicenca" AND !empty($this->id)) {
            $select .= " AND idLicenca <> {$this->id}";
        }

        $select .= " ORDER BY dtInicial";

        $afast = $pessoal->select($select, false);

        if (!empty($afast)) {
            # Verifica se é Licença ou afastamento
            if (mb_stripos($afast['nome'], 'Afastamento') === false) {
                $this->afastamento = "Licença";
            } else {
                $this->afastamento = "Afastamento";
            }
            $this->detalhe = $afast['nome'];

            if (empty($afast['numDias'])) {
                $this->periodo = date_to_php($afast['dtInicial']) . " a ???";
            } else {
                $this->periodo = date_to_php($afast['dtInicial']) . " a " . date_to_php($afast['dtFinal']) . " - " . $afast['numDias'] . " dias";
            }

            $this->tipo = $afast['tipo'];
            return true;
        }

        #################

        /*
         * Licença Médica Sem Alta
         */

        # Verifica se o servidor está em licença médica vencida sem alta
        # Pega a ultima licença médica (tipo 1, 2 ou 30) do servidor
        $select = "SELECT idLicenca, 
                          alta, 
                          dtInicial,
                          numDias,
                          ADDDATE(dtInicial,numDias-1) as dtFinal,
                          tblicenca.idTpLicenca as tipo
                      FROM tblicenca
                     WHERE idServidor = {$this->idServidor}
                       AND (idTpLicenca = 1 OR idTpLicenca = 2 OR idTpLicenca = 30)
                  ORDER BY dtInicial DESC LIMIT 1";
        $row2 = $pessoal->select($select, false);

        # Somente servidor Ativo
        if ($pessoal->get_idSituacao($this->idServidor) == 1) {

            # Verifica se retornou alguma licença
            if (!empty($row2)) {

                # Verifica se está em aberto
                if ($row2["alta"] <> 1) {

                    # Verifica se tem isenção - A isenção neste caso é particularmente diferente
                    if ($this->tabela == "tblicenca" AND $this->id == $row2["idLicenca"]) {
                        return false;
                    } else {
                        # Verifica se a licença editada é posterior a data em aberto
                        # Ou seja, se a data informada é posterior à data inicial da licença
                        if (dataMaior(date_to_php($row2["dtInicial"]), date_to_php($this->dtInicial)) == date_to_php($this->dtInicial)) {

                            # Agora Verifica se é a que está sendo incluída não é continuação desta ultima
                            if (!empty($this->tipoLicenca) AND ($this->tipoLicenca == 1 OR $this->tipoLicenca == 2 OR $this->tipoLicenca == 30)) {
                                return false;
                            } else {
                                # Se não for é uma outra licença sendo incluída sem que a licença anterior tenha alta
                                $this->afastamento = "Licença Em Aberto";
                                $this->detalhe = "Licença Médica Sem Alta";

                                if (empty($row2['numDias'])) {
                                    $this->periodo = date_to_php($row2['dtInicial']) . " a ???";
                                } else {
                                    $this->periodo = date_to_php($row2['dtInicial']) . " a " . date_to_php($row2['dtFinal']) . " - " . $row2['numDias'] . " dias";
                                }
                                $this->tipo = $afast['tipo'];
                                return true;
                            }
                        }

                        # Verifica se a data em aberto está entre as datas da licença editada
                        # Ou seja se a data informada está dentro do período da licença
                        if (entre(date_to_php($row2[2]), date_to_php($this->dtInicial), date_to_php($this->dtFinal))) {
                            $this->afastamento = "Licença Em Aberto";
                            $this->detalhe = "Licença Médica Sem Alta";

                            if (empty($row2['numDias'])) {
                                $this->periodo = date_to_php($row2['dtInicial']) . " a ???";
                            } else {
                                $this->periodo = date_to_php($row2['dtInicial']) . " a " . date_to_php($row2['dtFinal']) . " - " . $row2['numDias'] . " dias";
                            }
                            return true;
                        } else {
                            return false;
                        }
                    }
                }
            }
        }

        #################

        /*
         *  Licenças prêmio
         */
        $select = "SELECT idLicencaPremio,
                          dtInicial,
                          numDias,
                          ADDDATE(dtInicial,numDias-1) as dtFinal
                 FROM tblicencapremio
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1)))";

        // se tiver isenção
        if ($this->tabela == "tblicencapremio" AND !empty($this->id)) {
            $select .= " AND idLicencaPremio <> {$this->id}";
        }

        $select .= " ORDER BY dtInicial";

        $afast = $pessoal->select($select, false);

        if (!empty($afast)) {
            $this->afastamento = "Licença";
            $this->detalhe = "Licença Prêmio";

            if (empty($afast['numDias'])) {
                $this->periodo = date_to_php($afast['dtInicial']) . " a ???";
            } else {
                $this->periodo = date_to_php($afast['dtInicial']) . " a " . date_to_php($afast['dtFinal']) . " - " . $afast['numDias'] . " dias";
            }

            $this->tipo = 'prêmio';
            return true;
        }

        #################

        /*
         *  Licenças sem vencimentos
         */
        $select = "SELECT idLicencaSemVencimentos, 
                          tbtipolicenca.nome,
                          dtInicial,
                          numDias,
                          ADDDATE(dtInicial,numDias-1) as dtFinal
                     FROM tblicencasemvencimentos JOIN tbtipolicenca USING (idTpLicenca)
                    WHERE idServidor = {$this->idServidor}                      
                      AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                       OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                       OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1))
                       OR (dtInicial <= '{$this->dtFinal}' AND numDias IS NULL))";

        // se tiver isenção
        if ($this->tabela == "tblicencasemvencimentos" AND !empty($this->id)) {
            $select .= " AND idLicencaSemVencimentos <> {$this->id}";
        }

        $select .= " ORDER BY dtInicial";

        $afast = $pessoal->select($select, false);

        if (!empty($afast)) {
            $this->afastamento = "Licença";
            $this->detalhe = $afast['nome'];

            if (empty($afast['numDias'])) {
                $this->periodo = date_to_php($afast['dtInicial']) . " a ???";
            } else {
                $this->periodo = date_to_php($afast['dtInicial']) . " a " . date_to_php($afast['dtFinal']) . " - " . $afast['numDias'] . " dias";
            }
            $this->tipo = 'semVencimentos';
            return true;
        }

        #################

        /*
         *  Faltas Abonadas por atestado
         */
        $select = "SELECT idAtestado,
                          dtInicio,
                          numDias,
                          ADDDATE(dtInicio,numDias-1) as dtFinal
                 FROM tbatestado
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicio AND ADDDATE(dtInicio,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicio AND ADDDATE(dtInicio,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicio AND '{$this->dtFinal}' >= ADDDATE(dtInicio,numDias-1)))";

        // se tiver isenção
        if ($this->tabela == "tbatestado" AND !empty($this->id)) {
            $select .= " AND idAtestado <> {$this->id}";
        }

        $select .= " ORDER BY dtInicio";

        $afast = $pessoal->select($select, false);

        if (!empty($afast)) {
            $this->afastamento = "Falta Abonada";
            $this->detalhe = "Atestado Médico";

            if (empty($afast['numDias'])) {
                $this->periodo = date_to_php($afast['dtInicio']) . " a ???";
            } else {
                $this->periodo = date_to_php($afast['dtInicio']) . " a " . date_to_php($afast['dtFinal']) . " - " . $afast['numDias'] . " dias";
            }
            $this->tipo = 'atestado';
            return true;
        }

        #################

        /*
         *  Trabalho TRE
         */
        $select = "SELECT idTrabalhoTre,
                          data,
                          dias,
                          ADDDATE(data,dias-1) as dtFinal
                    FROM tbtrabalhotre
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN data AND ADDDATE(data,dias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN data AND ADDDATE(data,dias-1)) 
                     OR ('{$this->dtInicial}' <= data AND '{$this->dtFinal}' >= ADDDATE(data,dias-1)))";

        // se tiver isenção
        if ($this->tabela == "tbtrabalhotre" AND !empty($this->id)) {
            $select .= " AND idTrabalhoTre <> {$this->id}";
        }

        $select .= " ORDER BY data";

        $afast = $pessoal->select($select, false);

        if (!empty($afast)) {
            $this->afastamento = "TRE";
            $this->detalhe = "Trabalhando no TRE";

            if (empty($afast['dias'])) {
                $this->periodo = date_to_php($afast['data']) . " a ???";
            } else {
                $this->periodo = date_to_php($afast['data']) . " a " . date_to_php($afast['dtFinal']) . " - " . $afast['dias'] . " dias";
            }
            $this->tipo = 'tre';
            return true;
        }

        #################

        /*
         *  Folgas TRE
         */
        $select = "SELECT idFolga,
                          data,
                          dias,
                          ADDDATE(data,dias-1) as dtFinal
                 FROM tbfolga
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN data AND ADDDATE(data,dias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN data AND ADDDATE(data,dias-1)) 
                     OR ('{$this->dtInicial}' <= data AND '{$this->dtFinal}' >= ADDDATE(data,dias-1)))";

        // se tiver isenção
        if ($this->tabela == "tbfolga" AND !empty($this->id)) {
            $select .= " AND idFolga <> {$this->id}";
        }

        $select .= " ORDER BY data";

        $afast = $pessoal->select($select, false);

        if (!empty($afast)) {
            $this->afastamento = "Folga";
            $this->detalhe = "Em folga do TRE";

            if (empty($afast['dias'])) {
                $this->periodo = date_to_php($afast['data']) . " a ???";
            } else {
                $this->periodo = date_to_php($afast['data']) . " a " . date_to_php($afast['dtFinal']) . " - " . $afast['dias'] . " dias";
            }
            $this->tipo = 'tre';
            return true;
        }

        return false;
    }

    ###########################################################
}
