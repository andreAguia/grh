<?php

class Afastamento {

    /**
     * Abriga as várias rotina referentes ao afastamento do servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    private $idServidor = NULL;
    private $ano = NULL;
    private $mes = NULL;
    private $lotacao = NULL;
    private $linkEditar = NULL;
    private $idFuncional = TRUE;
    private $nomeSimples = FALSE;
    private $tipo = FALSE;          // Tipo do afastamento
    private $formulario = FALSE;

    ###########################################################

    /**
     * Método Construtor
     */
    public function __construct() {
        
    }

    ###########################################################

    public function set_mes($mes) {
        /**
         * Informa o mês do afastamento
         *
         * @param $mes string NULL O mês
         *
         * @syntax $input->set_mes($mes);
         */
        # Se vier vazio coloca o mês atual
        if (vazio($mes)) {
            $this->mes = date('m');
        } else {
            $this->mes = $mes;
        }
    }

    ###########################################################

    public function set_ano($ano) {
        /**
         * Informa o ano do afastamento
         *
         * @param $ano string NULL O ano
         *
         * @syntax $input->set_ano($ano);
         */
        # Se vier vazio coloca o ano atual
        if (vazio($ano)) {
            $this->ano = date('Y');
        } else {
            $this->ano = $ano;
        }
    }

    ###########################################################

    public function set_lotacao($lotacao) {
        /**
         * Informa a lotação dos servidores com afastamento
         *
         * @param $lotacao string NULL A lotacao
         *
         * @syntax $input->set_lotacao($lotacao);
         */
        $this->lotacao = $lotacao;
    }

    ###########################################################

    public function set_linkEditar($linkEditar) {
        /**
         * Informa a rotina de edição (se houver)
         *
         * @param $linkEditar string NULL O link da rotina de edição
         *
         * @syntax $input->set_linkEditar($linkEditar);
         */
        $this->linkEditar = $linkEditar;
    }

    ###########################################################

    public function set_idFuncional($idFuncional) {
        /**
         * Informa se terá a coluna de idFuncional ou não. Usado para economizar espaço
         *
         * @param $idFuncional bool NULL Se terá ou não
         *
         * @syntax $input->set_idFuncional($idFuncional);
         */
        $this->idFuncional = $idFuncional;
    }

    ###########################################################

    public function set_nomeSimples($nomeSimples) {
        /**
         * Informa se terá o nome simples ou completo do servidor
         *
         * @param $nomeSimples bool NULL Se terá ou não
         *
         * @syntax $input->set_nomeSimples($nomeSimples);
         */
        $this->nomeSimples = $nomeSimples;
    }

    ###########################################################

    public function set_formulario($formulario) {
        /**
         * Informa se terá ou não formulário no relatório
         *
         * @param $formulario bool FALSE Se terá ou não
         *
         * @syntax $input->set_formulario($formulario);
         */
        $this->formulario = $formulario;
    }

    ###########################################################

    public function set_campoMes($campoMes) {
        /**
         * Informa se terá ou não campo mês no formulário no relatório
         *
         * @param $campoMes bool FALSE Se terá ou não
         *
         * @syntax $input->set_campoMes($campoMes);
         */
        $this->campoMes = $campoMes;
    }

    ###########################################################

    public function set_campoAno($campoAno) {
        /**
         * Informa se terá ou não campo mês no formulário no relatório
         *
         * @param $campoAno bool FALSE Se terá ou não
         *
         * @syntax $input->set_campoAno($campoAno);
         */
        $this->campoAno = $campoAno;
    }

    ###########################################################

    public function set_idServidor($idServidor) {
        /**
         * Informa se será de um servidor ou de todos
         *
         * @param $idServidor string NULL A id do servidor
         *
         * @syntax $input->set_idServidor($idServidor);
         */
        $this->idServidor = $idServidor;
    }

    ###########################################################

    public function set_tipo($tipo) {
        /**
         * Informa o tipo do afastamento
         *
         * @param $tipo string NULL o tipo
         *
         * @syntax $afastamento->set_tipo($tipo);
         */
        $this->tipo = $tipo;
    }

    ###########################################################

    public function montaSelect() {

        /**
         * monta o select para toda a classe
         *
         * @syntax $input->exibeTabela();
         */
        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        # Constroi a data
        if (!vazio($this->mes)) {
            $data = $this->ano . '-' . $this->mes . '-01';
        }

        # Se for de um só servidor não exibe o idFuncional
        if (!vazio($this->idServidor)) {
            $this->idFuncional = FALSE;
        }

        ###############################################################33
        # Licença Geral

        $select = '(SELECT ';

        if ($this->idFuncional) {
            $select .= 'tbservidor.idfuncional,';
        }

        if (vazio($this->idServidor)) {
            $select .= ' tbpessoa.nome,
                         tbservidor.idServidor,';
        }

        #######################
        # Licença
        $select .= '       tblicenca.dtInicial,
                           tblicenca.numDias,
                           ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                           CONCAT(tbtipolicenca.nome,"<br/>",IFNULL(tbtipolicenca.lei,""),IF(alta=1," - Com Alta"," - Sem Alta")),
                          tbservidor.idServidor
                      FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                           JOIN tbhistlot USING (idServidor)
                                           JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                      LEFT JOIN tblicenca USING (idServidor)
                                      LEFT JOIN tbtipolicenca USING (idTpLicenca)
                    WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

        # Tipo de afastamento
        if (!vazio($this->tipo)) {
            if (is_numeric($this->tipo)) {
                if (($this->tipo <> 6) AND ($this->tipo <> 5) AND ($this->tipo <> 8) AND ($this->tipo <> 16)) {
                    $select .= ' AND idTpLicenca = ' . $this->tipo;
                } else {
                    $select .= ' AND FALSE';
                }
            } else {
                $select .= ' AND FALSE';
            }
        }

        # Verifica se é somente de um servidor
        if (vazio($this->idServidor)) {
            $select .= ' AND tbservidor.situacao = 1';
        } else {
            $select .= ' AND tbservidor.idServidor = ' . $this->idServidor;
            $select .= ' AND tblicenca.dtInicial IS NOT NULL';
        }

        if (!vazio($this->mes)) {
            $select .= ' AND (("' . $data . '" BETWEEN tblicenca.dtInicial AND ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1))
                          OR  (LAST_DAY("' . $data . '") BETWEEN tblicenca.dtInicial AND ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1))
                          OR  ("' . $data . '" < tblicenca.dtInicial AND LAST_DAY("' . $data . '") > ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)))';
        } elseif (!vazio($this->ano)) {
            $select .= ' AND (((YEAR(tblicenca.dtInicial) = ' . $this->ano . ') OR (YEAR(ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)) = ' . $this->ano . ')) 
                          OR ((YEAR(tblicenca.dtInicial) < ' . $this->ano . ') AND (YEAR(ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)) > ' . $this->ano . ')))';
        }

        # lotacao
        if (!is_null($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        #######################    
        # Licença Prêmio
        $select .= ') UNION (
                  SELECT ';

        if ($this->idFuncional) {
            $select .= 'tbservidor.idfuncional,';
        }

        if (vazio($this->idServidor)) {
            $select .= ' tbpessoa.nome,
                          tbservidor.idServidor,';
        }

        $select .= '     tblicencapremio.dtInicial,
                         tblicencapremio.numDias,
                         ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1),
                         (SELECT CONCAT(tbtipolicenca.nome,"<br/>",IFNULL(tbtipolicenca.lei,"")) FROM tbtipolicenca WHERE idTpLicenca = 6),
                          tbservidor.idServidor
                    FROM tbtipolicenca,tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                                       JOIN tbhistlot USING (idServidor)
                                                       JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                                  LEFT JOIN tblicencapremio USING (idServidor)
                    WHERE tbtipolicenca.idTpLicenca = 6 AND tbservidor.situacao = 1
                      AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

        # Tipo de afastamento
        if (!vazio($this->tipo)) {
            if (is_numeric($this->tipo)) {
                if ($this->tipo == 6) {
                    $select .= ' AND TRUE';
                } else {
                    $select .= ' AND FALSE';
                }
            } else {
                $select .= ' AND FALSE';
            }
        }

        # Verifica se é somente de um servidor
        if (vazio($this->idServidor)) {
            $select .= ' AND tbservidor.situacao = 1';
        } else {
            $select .= ' AND tbservidor.idServidor = ' . $this->idServidor;
            $select .= ' AND tblicencapremio.dtInicial IS NOT NULL';
        }

        if (!vazio($this->mes)) {
            $select .= '       
                      AND (("' . $data . '" BETWEEN tblicencapremio.dtInicial AND ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1))
                       OR  (LAST_DAY("' . $data . '") BETWEEN tblicencapremio.dtInicial AND ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1))
                       OR  ("' . $data . '" < tblicencapremio.dtInicial AND LAST_DAY("' . $data . '") > ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1)))';
        } elseif (!vazio($this->ano)) {
            $select .= ' AND (((YEAR(tblicencapremio.dtInicial) = ' . $this->ano . ') OR (YEAR(ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1)) = ' . $this->ano . ')) 
                         OR ((YEAR(tblicencapremio.dtInicial) < ' . $this->ano . ') AND (YEAR(ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1)) > ' . $this->ano . ')))';
        }

        # lotacao
        if (!is_null($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        #######################
        # Férias
        $select .= ') UNION (
                   SELECT ';

        if ($this->idFuncional) {
            $select .= 'tbservidor.idfuncional,';
        }

        if (vazio($this->idServidor)) {
            $select .= ' tbpessoa.nome,
                          tbservidor.idServidor,';
        }

        $select .= '      tbferias.dtInicial,
                          tbferias.numDias,
                          ADDDATE(tbferias.dtInicial,tbferias.numDias-1),
                          CONCAT("Férias ",tbferias.anoExercicio),
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                          JOIN tbhistlot USING (idServidor)
                                          JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     LEFT JOIN tbferias USING (idServidor)
                    WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

        # Tipo de afastamento
        if (!vazio($this->tipo)) {
            if (is_numeric($this->tipo)) {
                $select .= ' AND FALSE';
            } else {
                if ($this->tipo == 'ferias') {
                    $select .= ' AND TRUE';
                } else {
                    $select .= ' AND FALSE';
                }
            }
        }

        # Verifica se é somente de um servidor
        if (vazio($this->idServidor)) {
            $select .= ' AND tbservidor.situacao = 1';
        } else {
            $select .= ' AND tbservidor.idServidor = ' . $this->idServidor;
            $select .= ' AND tbferias.dtInicial IS NOT NULL';
        }

        if (!vazio($this->mes)) {
            $select .= '       
                      AND (("' . $data . '" BETWEEN tbferias.dtInicial AND ADDDATE(tbferias.dtInicial,tbferias.numDias-1))
                       OR  (LAST_DAY("' . $data . '") BETWEEN tbferias.dtInicial AND ADDDATE(tbferias.dtInicial,tbferias.numDias-1))
                       OR  ("' . $data . '" < tbferias.dtInicial AND LAST_DAY("' . $data . '") > ADDDATE(tbferias.dtInicial,tbferias.numDias-1)))';
        } elseif (!vazio($this->ano)) {
            $select .= ' AND (((YEAR(tbferias.dtInicial) = ' . $this->ano . ') OR (YEAR(ADDDATE(tbferias.dtInicial,tbferias.numDias-1)) = ' . $this->ano . ')) 
                         OR ((YEAR(tbferias.dtInicial) < ' . $this->ano . ') AND (YEAR(ADDDATE(tbferias.dtInicial,tbferias.numDias-1)) > ' . $this->ano . ')))';
        }

        # lotacao
        if (!is_null($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        #######################
        # Faltas abonadas
        $select .= ') UNION (
                   SELECT ';

        if ($this->idFuncional) {
            $select .= 'tbservidor.idfuncional,';
        }

        if (vazio($this->idServidor)) {
            $select .= ' tbpessoa.nome,
                          tbservidor.idServidor,';
        }

        $select .= '     tbatestado.dtInicio,
                          tbatestado.numDias,
                          ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1),
                          "Falta Abonada",
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                          JOIN tbhistlot USING (idServidor)
                                          JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     LEFT JOIN tbatestado USING (idServidor)
                    WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

        # Tipo de afastamento
        if (!vazio($this->tipo)) {
            if (is_numeric($this->tipo)) {
                $select .= ' AND FALSE';
            } else {
                if ($this->tipo == 'faltas') {
                    $select .= ' AND TRUE';
                } else {
                    $select .= ' AND FALSE';
                }
            }
        }

        # Verifica se é somente de um servidor
        if (vazio($this->idServidor)) {
            $select .= ' AND tbservidor.situacao = 1';
        } else {
            $select .= ' AND tbservidor.idServidor = ' . $this->idServidor;
            $select .= ' AND tbatestado.dtInicio IS NOT NULL';
        }

        if (!vazio($this->mes)) {
            $select .= '       
                      AND (("' . $data . '" BETWEEN tbatestado.dtInicio AND ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1))
                       OR  (LAST_DAY("' . $data . '") BETWEEN tbatestado.dtInicio AND ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1))
                       OR  ("' . $data . '" < tbatestado.dtInicio AND LAST_DAY("' . $data . '") > ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1)))';
        } elseif (!vazio($this->ano)) {
            $select .= ' AND (((YEAR(tbatestado.dtInicio) = ' . $this->ano . ') OR (YEAR(ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1)) = ' . $this->ano . ')) 
                         OR ((YEAR(tbatestado.dtInicio) < ' . $this->ano . ') AND (YEAR(ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1)) > ' . $this->ano . ')))';
        }

        # lotacao
        if (!is_null($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        #######################
        # Trabalhando TRE
        $select .= ') UNION (
                   SELECT ';

        if ($this->idFuncional) {
            $select .= 'tbservidor.idfuncional,';
        }

        if (vazio($this->idServidor)) {
            $select .= ' tbpessoa.nome,
                          tbservidor.idServidor,';
        }

        $select .= '     tbtrabalhotre.data,
                          tbtrabalhotre.dias,
                          ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1),
                          "Trabalhando no TRE",
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                          JOIN tbhistlot USING (idServidor)
                                          JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     LEFT JOIN tbtrabalhotre USING (idServidor)
                    WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

        # Tipo de afastamento
        if (!vazio($this->tipo)) {
            if (is_numeric($this->tipo)) {
                $select .= ' AND FALSE';
            } else {
                if ($this->tipo == 'TTRE') {
                    $select .= ' AND TRUE';
                } else {
                    $select .= ' AND FALSE';
                }
            }
        }

        # Verifica se é somente de um servidor
        if (vazio($this->idServidor)) {
            $select .= ' AND tbservidor.situacao = 1';
        } else {
            $select .= ' AND tbservidor.idServidor = ' . $this->idServidor;
            $select .= ' AND tbtrabalhotre.data IS NOT NULL';
        }

        if (!vazio($this->mes)) {
            $select .= '       
                      AND (("' . $data . '" BETWEEN tbtrabalhotre.data AND ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1))
                       OR  (LAST_DAY("' . $data . '") BETWEEN tbtrabalhotre.data AND ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1))
                       OR  ("' . $data . '" < tbtrabalhotre.data AND LAST_DAY("' . $data . '") > ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1)))';
        } elseif (!vazio($this->ano)) {
            $select .= ' AND (((YEAR(tbtrabalhotre.data) = ' . $this->ano . ') OR (YEAR(ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1)) = ' . $this->ano . ')) 
                         OR ((YEAR(tbtrabalhotre.data) < ' . $this->ano . ') AND (YEAR(ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1)) > ' . $this->ano . ')))';
        }

        # lotacao
        if (!is_null($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        #######################
        # Folga TRE
        $select .= ') UNION (
                   SELECT ';

        if ($this->idFuncional) {
            $select .= 'tbservidor.idfuncional,';
        }

        if (vazio($this->idServidor)) {
            $select .= ' tbpessoa.nome,
                          tbservidor.idServidor,';
        }

        $select .= '      tbfolga.data,
                          tbfolga.dias,
                          ADDDATE(tbfolga.data,tbfolga.dias-1),
                          "Folga TRE",
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                          JOIN tbhistlot USING (idServidor)
                                          JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     LEFT JOIN tbfolga USING (idServidor)
                    WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

        # Tipo de afastamento
        if (!vazio($this->tipo)) {
            if (is_numeric($this->tipo)) {
                $select .= ' AND FALSE';
            } else {
                if ($this->tipo == 'FTRE') {
                    $select .= ' AND TRUE';
                } else {
                    $select .= ' AND FALSE';
                }
            }
        }

        # Verifica se é somente de um servidor
        if (vazio($this->idServidor)) {
            $select .= ' AND tbservidor.situacao = 1';
        } else {
            $select .= ' AND tbservidor.idServidor = ' . $this->idServidor;
            $select .= ' AND tbfolga.data IS NOT NULL';
        }

        if (!vazio($this->mes)) {
            $select .= '       
                      AND (("' . $data . '" BETWEEN tbfolga.data AND ADDDATE(tbfolga.data,tbfolga.dias-1))
                       OR  (LAST_DAY("' . $data . '") BETWEEN tbfolga.data AND ADDDATE(tbfolga.data,tbfolga.dias-1))
                       OR  ("' . $data . '" < tbfolga.data AND LAST_DAY("' . $data . '") > ADDDATE(tbfolga.data,tbfolga.dias-1)))';
        } elseif (!vazio($this->ano)) {
            $select .= ' AND (((YEAR(tbfolga.data) = ' . $this->ano . ') OR (YEAR(ADDDATE(tbfolga.data,tbfolga.dias-1)) = ' . $this->ano . ')) 
                         OR ((YEAR(tbfolga.data) < ' . $this->ano . ') AND (YEAR(ADDDATE(tbfolga.data,tbfolga.dias-1)) > ' . $this->ano . ')))';
        }

        # lotacao
        if (!is_null($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        #######################
        # Licença sem vencimentos
        $select .= ') UNION (
                   SELECT ';

        if ($this->idFuncional) {
            $select .= 'tbservidor.idfuncional,';
        }

        if (vazio($this->idServidor)) {
            $select .= ' tbpessoa.nome,
                          tbservidor.idServidor,';
        }

        $select .= '     tblicencasemvencimentos.dtInicial,
                                   tblicencasemvencimentos.numDias,
                                   ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1),
                                   tbtipolicenca.nome,
                                   tbservidor.idServidor
                              FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                                   JOIN tbhistlot USING (idServidor)
                                                   JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                              LEFT JOIN tblicencasemvencimentos USING (idServidor)
                                              LEFT JOIN tbperfil USING (idPerfil)
                                                   JOIN tbtipolicenca ON (tblicencasemvencimentos.idTpLicenca = tbtipolicenca.idTpLicenca)
                             WHERE (tbtipolicenca.idTpLicenca = 5 OR tbtipolicenca.idTpLicenca = 8 OR tbtipolicenca.idTpLicenca = 16)           
                               AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

        # Tipo de afastamento
        if (!vazio($this->tipo)) {
            if (is_numeric($this->tipo)) {
                if (($this->tipo == 5) OR ($this->tipo == 8) OR ($this->tipo == 16)) {
                    $select .= ' AND idTpLicenca = ' . $this->tipo;
                } else {
                    $select .= ' AND FALSE';
                }
            } else {
                $select .= ' AND FALSE';
            }
        }


        # Verifica se é somente de um servidor
        if (vazio($this->idServidor)) {
            $select .= ' AND tbservidor.situacao = 1';
        } else {
            $select .= ' AND tbservidor.idServidor = ' . $this->idServidor;
            $select .= ' AND tblicencasemvencimentos.dtInicial IS NOT NULL';
        }

        if (!vazio($this->mes)) {
            $select .= '       
                               AND (("' . $data . '" BETWEEN tblicencasemvencimentos.dtInicial AND ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1))
                                OR  (LAST_DAY("' . $data . '") BETWEEN tblicencasemvencimentos.dtInicial AND ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1))
                                OR  ("' . $data . '" < tblicencasemvencimentos.dtInicial AND LAST_DAY("' . $data . '") > ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1)))';
        } elseif ($this->ano) {
            $select .= ' AND (((YEAR(tblicencasemvencimentos.dtInicial) = ' . $this->ano . ') OR (YEAR(ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1)) = ' . $this->ano . ')) 
                         OR ((YEAR(tblicencasemvencimentos.dtInicial) < ' . $this->ano . ') AND (YEAR(ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1)) > ' . $this->ano . ')))';
        }

        # lotacao
        if (!is_null($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        if ($this->idFuncional) {
            $select .= ') ORDER BY 2, 3';
        } else {
            $select .= ') ORDER BY 1, 2';
        }
        #echo $select;
        return $select;
    }

    ###########################################################

    public function exibeTabela() {

        /**
         * Exibe uma tabela com a relação dos servidores comafastamento
         *
         * @syntax $input->exibeTabela();
         */
        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        $select = $this->montaSelect();

        $result = $pessoal->select($select);
        $cont = $pessoal->count($select);

        $tabela = new Tabela();
        if (vazio($this->idServidor)) {
            $titulo = 'Servidores com Afastamentos';
        } else {
            $titulo = 'Afastamentos';
        }

        if (!vazio($this->mes)) {
            $titulo .= " " . get_nomeMes($this->mes) . "/" . $this->ano;
        } elseif ($this->ano) {
            $titulo .= " " . $this->ano;
        }

        $tabela->set_titulo($titulo);

        if (vazio($this->idServidor)) {

            if ($this->idFuncional) {

                $tabela->set_label(array('IdFuncional', 'Nome', 'Lotação', 'Data Inicial', 'Dias', 'Data Final', 'Descrição'));
                $tabela->set_align(array('center', 'left', 'left', 'center', 'center', 'center', 'left'));

                $tabela->set_classe(array(NULL, NULL, "pessoal"));
                $tabela->set_metodo(array(NULL, NULL, "get_lotacaoSimples"));

                if ($this->nomeSimples) {
                    $tabela->set_funcao(array(NULL, "get_nomeSimples", NULL, "date_to_php", NULL, "date_to_php"));
                } else {
                    $tabela->set_funcao(array(NULL, NULL, NULL, "date_to_php", NULL, "date_to_php"));
                }

                $tabela->set_rowspan(1);
                $tabela->set_grupoCorColuna(1);
            } else {

                $tabela->set_label(array('Nome', NULL, 'Data Inicial', 'Dias', 'Data Final', 'Descrição'));
                $tabela->set_align(array('left', 'left', 'center', 'center', 'center', 'left'));

                $tabela->set_classe(array(NULL, "pessoal"));
                $tabela->set_metodo(array(NULL, "get_lotacaoSimples"));

                if ($this->nomeSimples) {
                    $tabela->set_funcao(array("get_nomeSimples", NULL, "date_to_php", NULL, "date_to_php"));
                } else {
                    $tabela->set_funcao(array(NULL, NULL, "date_to_php", NULL, "date_to_php"));
                }

                $tabela->set_rowspan(0);
                $tabela->set_grupoCorColuna(0);
            }
        } else {

            $tabela->set_label(array('Data Inicial', 'Dias', 'Data Final', 'Descrição'));
            $tabela->set_align(array('center', 'center', 'center', 'left'));
            $tabela->set_funcao(array("date_to_php", NULL, "date_to_php"));
            $tabela->set_width(array(15, 5, 15, 65));
        }

        if (!vazio($this->linkEditar)) {
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar($this->linkEditar);
        }

        $tabela->set_conteudo($result);
        $tabela->show();
    }

    ###########################################################

    public function exibeRelatorio() {

        /**
         * Exibe um relatório com a relação dos servidores com afastamento
         *
         * @syntax $afast->exibeRelatorio();
         */
        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        $select = $this->montaSelect();

        $relatorio = new Relatorio();
        $relatorio->set_titulo('Servidores com Afastamentos');

        if (is_numeric($this->lotacao)) {
            $tt = $pessoal->get_nomeLotacao($this->lotacao);
        } else {
            $tt = $this->lotacao;
        }
        $relatorio->set_subtitulo($tt);

        $nomeMes = get_nomeMes($this->mes);

        if ($this->mes) {
            $relatorio->set_tituloLinha2($nomeMes . ' / ' . $this->ano);
        } else {
            $relatorio->set_tituloLinha2($this->ano);
        }

        if ($this->idFuncional) {
            $relatorio->set_label(array('IdFuncional', 'Nome', 'Lotação', 'Data Inicial', 'Dias', 'Data Final', 'Descrição'));
            $relatorio->set_align(array('center', 'left', 'left', 'center', 'center', 'center', 'left'));

            $relatorio->set_classe(array(NULL, NULL, "pessoal"));
            $relatorio->set_metodo(array(NULL, NULL, "get_lotacaoSimples"));

            if ($this->nomeSimples) {
                $relatorio->set_funcao(array(NULL, "get_nomeSimples", NULL, "date_to_php", NULL, "date_to_php"));
            } else {
                $relatorio->set_funcao(array(NULL, NULL, NULL, "date_to_php", NULL, "date_to_php"));
            }

            #$relatorio->set_rowspan(1);
            #$relatorio->set_grupoCorColuna(1);
        } else {

            $relatorio->set_label(array('Nome', 'Lotação', 'Data Inicial', 'Dias', 'Data Final', 'Descrição'));
            $relatorio->set_align(array('left', 'left', 'center', 'center', 'center', 'left'));

            $relatorio->set_classe(array(NULL, "pessoal"));
            $relatorio->set_metodo(array(NULL, "get_lotacaoSimples"));

            if ($this->nomeSimples) {
                $relatorio->set_funcao(array("get_nomeSimples", NULL, "date_to_php", NULL, "date_to_php"));
            } else {
                $relatorio->set_funcao(array(NULL, NULL, "date_to_php", NULL, "date_to_php"));
            }

            #$relatorio->set_rowspan(0);
            #$relatorio->set_grupoCorColuna(0);
        }

        $result = $pessoal->select($select);
        $cont = $pessoal->count($select);

        $relatorio->set_conteudo($result);

        if ($this->formulario) {
            # Dados da combo lotacao
            $lotacao = $pessoal->select('(SELECT idlotacao, concat(IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
                                            FROM tblotacao
                                           WHERE ativo) UNION (SELECT distinct DIR, DIR
                                            FROM tblotacao
                                           WHERE ativo)
                                        ORDER BY 2');
            array_unshift($lotacao, array('*', '-- Todos --'));

            # Cria array dos meses
            $mes = array(array("1", "Janeiro"),
                array("2", "Fevereiro"),
                array("3", "Março"),
                array("4", "Abril"),
                array("5", "Maio"),
                array("6", "Junho"),
                array("7", "Julho"),
                array("8", "Agosto"),
                array("9", "Setembro"),
                array("10", "Outubro"),
                array("11", "Novembro"),
                array("12", "Dezembro"));

            #$relatorio->set_bordaInterna(TRUE);
            #$relatorio->set_cabecalho(FALSE);

            if ($this->campoMes) {
                $relatorio->set_formCampos(array(
                    array('nome' => 'ano',
                        'label' => 'Ano:',
                        'tipo' => 'texto',
                        'size' => 4,
                        'title' => 'Ano',
                        'col' => 3,
                        'padrao' => $this->ano,
                        'onChange' => 'formPadrao.submit();',
                        'linha' => 1),
                    array('nome' => 'mes',
                        'label' => 'Mês',
                        'tipo' => 'combo',
                        'array' => $mes,
                        'col' => 3,
                        'size' => 10,
                        'padrao' => $this->mes,
                        'title' => 'Mês do Ano.',
                        'onChange' => 'formPadrao.submit();',
                        'linha' => 1),
                    array('nome' => 'lotacao',
                        'label' => 'Lotação',
                        'tipo' => 'combo',
                        'array' => $lotacao,
                        'col' => 6,
                        'size' => 50,
                        'padrao' => $this->lotacao,
                        'title' => 'Filtra por Lotação.',
                        'onChange' => 'formPadrao.submit();',
                        'linha' => 1)));
            } else {
                $relatorio->set_formCampos(array(
                    array('nome' => 'ano',
                        'label' => 'Ano:',
                        'tipo' => 'texto',
                        'size' => 4,
                        'title' => 'Ano',
                        'col' => 3,
                        'padrao' => $this->ano,
                        'onChange' => 'formPadrao.submit();',
                        'linha' => 1),
                    array('nome' => 'lotacao',
                        'label' => 'Lotação',
                        'tipo' => 'combo',
                        'array' => $lotacao,
                        'col' => 6,
                        'size' => 50,
                        'padrao' => $this->lotacao,
                        'title' => 'Filtra por Lotação.',
                        'onChange' => 'formPadrao.submit();',
                        'linha' => 1)));
            }

            $relatorio->set_formFocus('ano');
            $relatorio->set_formLink('?');
        }

        if ($cont > 0) {
            $relatorio->show();
        } else {
            titulotable('Servidores com Afastamentos');
            callout("Nenhum valor a ser exibido !", "secondary");
        }
    }

    ###########################################################

    public function exibeTimeline() {

        /**
         * Exibe um relatório com a relação dos servidores com afastamento
         *
         * @syntax $afast->exibeRelatorio();
         */
        $grid = new Grid();
        $grid->abreColuna(12);

        #tituloTable("Afastamentos Anuais");
        # Gráfico
        $select1 = "(SELECT CONCAT('Férias',' - ',anoExercicio) as descricao,
                              dtInicial,
                              numDias,
                              ADDDATE(dtInicial,numDias-1) as dtFinal
                         FROM tbferias
                        WHERE idServidor = $this->idServidor
                          AND (((YEAR(tbferias.dtInicial) = $this->ano) OR (YEAR(ADDDATE(tbferias.dtInicial,tbferias.numDias-1)) = $this->ano)) 
                           OR ((YEAR(tbferias.dtInicial) < $this->ano) AND (YEAR(ADDDATE(tbferias.dtInicial,tbferias.numDias-1)) > $this->ano)))  
                     ORDER BY dtInicial) UNION 
                       (SELECT CONCAT(tbtipolicenca.nome,' ',IFNULL(tbtipolicenca.lei,'')) as descricao,
                              dtInicial,
                              numDias,
                              ADDDATE(dtInicial,numDias-1) as dtFinal
                         FROM tblicenca LEFT JOIN tbtipolicenca USING(idTpLicenca) 
                        WHERE idServidor = $this->idServidor
                           AND (((YEAR(tblicenca.dtInicial) = $this->ano) OR (YEAR(ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)) = $this->ano)) 
                           OR ((YEAR(tblicenca.dtInicial) < $this->ano) AND (YEAR(ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)) > $this->ano)))   
                     ORDER BY dtInicial) UNION 
                       (SELECT 'Licença Prêmio' as descricao,
                              dtInicial,
                              numDias,
                              ADDDATE(dtInicial,numDias-1) as dtFinal
                         FROM tblicencapremio
                        WHERE idServidor = $this->idServidor
                          AND (((YEAR(tblicencapremio.dtInicial) = $this->ano) OR (YEAR(ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1)) = $this->ano)) 
                           OR ((YEAR(tblicencapremio.dtInicial) < $this->ano) AND (YEAR(ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1)) > $this->ano)))  
                     ORDER BY dtInicial) UNION 
                       (SELECT 'Trabalho TRE' as descricao,
                              data,
                              dias,
                              ADDDATE(data,dias-1) as dtFinal
                         FROM tbtrabalhotre
                        WHERE idServidor = $this->idServidor
                          AND (((YEAR(tbtrabalhotre.data) = $this->ano) OR (YEAR(ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1)) = $this->ano)) 
                           OR ((YEAR(tbtrabalhotre.data) < $this->ano) AND (YEAR(ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1)) > $this->ano)))   
                     ORDER BY data) UNION 
                       (SELECT 'Folga TRE' as descricao,
                              data,
                              dias,
                              ADDDATE(data,dias-1) as dtFinal
                         FROM tbfolga
                        WHERE idServidor = $this->idServidor
                          AND (((YEAR(tbfolga.data) = $this->ano) OR (YEAR(ADDDATE(tbfolga.data,tbfolga.dias-1)) = $this->ano)) 
                           OR ((YEAR(tbfolga.data) < $this->ano) AND (YEAR(ADDDATE(tbfolga.data,tbfolga.dias-1)) > $this->ano)))      
                     ORDER BY data) UNION 
                       (SELECT 'Folga Abonadas' as descricao,
                              dtInicio,
                              numDias,
                              ADDDATE(dtInicio,numDias-1) as dtFinal
                         FROM tbatestado
                        WHERE idServidor = $this->idServidor
                          AND (((YEAR(tbatestado.dtInicio) = $this->ano) OR (YEAR(ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1)) = $this->ano)) 
                           OR ((YEAR(tbatestado.dtInicio) < $this->ano) AND (YEAR(ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1)) > $this->ano)))    
                     ORDER BY dtInicio)  UNION 
                       (SELECT 'Licença Sem Vencimentos' as descricao,
                              dtInicial,
                              numDias,
                              ADDDATE(dtInicial,numDias-1) as dtFinal
                         FROM tblicencasemvencimentos
                        WHERE idServidor = $this->idServidor
                          AND (((YEAR(tblicencasemvencimentos.dtInicial) = $this->ano) OR (YEAR(ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1)) = $this->ano)) 
                           OR ((YEAR(tblicencasemvencimentos.dtInicial) < $this->ano) AND (YEAR(ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1)) > $this->ano)))  
                     ORDER BY dtInicial)
                        order by 2";

        # Acessa o banco
        $pessoal = new Pessoal();
        $atividades1 = $pessoal->select($select1);
        $numAtividades = $pessoal->count($select1);
        $contador = $numAtividades; // Contador pra saber quando tirar a virgula no último valor do for each linhas abaixo.
        #tituloTable("Afastamentos de $parametroAno");

        if ($numAtividades > 0) {

            # Carrega a rotina do Google
            echo '<script type="text/javascript" src="' . PASTA_FUNCOES_GERAIS . '/loader.js"></script>';

            # Inicia o script
            echo "<script type='text/javascript'>";
            echo "google.charts.load('current', {'packages':['timeline'], 'language': 'pt-br'});
                      google.charts.setOnLoadCallback(drawChart);
                      function drawChart() {
                            var container = document.getElementById('timeline');
                            var chart = new google.visualization.Timeline(container);
                            var dataTable = new google.visualization.DataTable();";

            echo "dataTable.addColumn({ type: 'string' });
                      dataTable.addColumn({ type: 'string', role: 'tooltip' });
                      dataTable.addColumn({ type: 'date' });
                      dataTable.addColumn({ type: 'date' });";

            echo "dataTable.addRows([";

            $separador = '-';

            foreach ($atividades1 as $row) {

                # Trata a data inicial
                $dt1 = explode($separador, $row['dtInicial']);
                $dt2 = explode($separador, $row['dtFinal']);

                echo "['" . $row['descricao'] . "','Teste', new Date($dt1[0], $dt1[1]-1, $dt1[2]), new Date($dt2[0], $dt2[1]-1, $dt2[2])]";

                $contador--;

                if ($contador > 0) {
                    echo ",";
                }
            }
            echo "]);";

            echo "chart.draw(dataTable);";
            echo "}";
            echo "</script>";

            #[ 'Washington', new Date(1789, 3, 30), new Date(1797, 2, 4) ],
            #[ 'Adams',      new Date(1797, 2, 4),  new Date(1801, 2, 4) ],
            #[ 'Jefferson',  new Date(1801, 2, 4),  new Date(1809, 2, 4) ]]);

            $altura = ($numAtividades * 45) + 50;
            echo '<div id="timeline" style="height: ' . $altura . 'px; width: 100%;"></div>';
        } else {
            br();
            p("Não há dados para serem exibidos.", "f14", "center");
        }

        $grid->fechaColuna();
        $grid->fechaGrid();
    }

    ###########################################################
}
