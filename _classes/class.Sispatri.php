<?php

class Sispatri {

    /**
     * Abriga as várias rotina do COntrole Sispatri
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    private $lotacao = null;
    private $situacao = null;
    private $matNomeId = null;
    private $ordenacao = "nome";
    private $exibeAfastamento = true;
    private $exibeEmail = true;

###########################################################

    /**
     * Método set_lotacao
     * 
     * @param $lotacao 
     */
    public function set_lotacao($lotacao) {
        if ($lotacao <> "Todos") {
            $this->lotacao = $lotacao;
        }
    }

###########################################################

    /**
     * Método set_situacao
     * 
     * @param  	$situacao
     */
    public function set_situacao($situacao) {
        if ($situacao <> "Todos") {
            $this->situacao = $situacao;
        }
    }

###########################################################

    /**
     * Método set_lotacao
     * 
     * @param $matNomeId 
     */
    public function set_matNomeId($matNomeId) {
        if (!empty($matNomeId)) {
            $this->matNomeId = $matNomeId;
        }
    }

###########################################################

    /**
     * Método set_ordenacao
     * 
     * @param  	$ordenacao
     */
    public function set_ordenacao($ordenacao) {
        $this->ordenacao = $ordenacao;
    }

###########################################################

    /**
     * Método exibeAfastamento
     * 
     * Informa se exibe ou não o campo de afastamento na listagem
     * 
     * @param $exibeAfastamento 
     */
    public function exibeAfastamento($exibeAfastamento) {
        $this->exibeAfastamento = $exibeAfastamento;
    }

###########################################################

    /**
     * Método exibeEmail
     * 
     * Informa se exibe ou não o campo e-mail na listagem
     * 
     * @param $exibeEmail 
     */
    public function exibeEmail($exibeEmail) {
        $this->exibeEmail = $exibeEmail;
    }

###########################################################

    public function get_servidoresEntregaramAtivos() {

        # Pega os dados
        $select = 'SELECT tbservidor.idfuncional,
                         tbpessoa.nome,
                         tbservidor.idServidor,
                         concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")) lotacao,
                         tbservidor.idServidor
                    FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                         JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                   AND tbservidor.situacao = 1';

        # Lotacao
        if (!empty($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        # Matrícula, nome ou id
        if (!is_null($this->matNomeId)) {
            if (is_numeric($this->matNomeId)) {
                $select .= ' AND ((';
                $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
            } else {

                # Verifica se tem espaços
                if (strpos($this->matNomeId, ' ') !== false) {
                    # Separa as palavras
                    $palavras = explode(' ', $this->matNomeId);

                    # Percorre as palavras
                    foreach ($palavras as $item) {
                        $select .= 'AND (tbpessoa.nome LIKE "%' . $item . '%")';
                    }
                } else {
                    $select .= ' AND (';
                    $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
                }
            }

            # Faz pesquisa na matricula e outros
            if (is_numeric($this->matNomeId)) {
                $select .= ' OR (tbservidor.matricula LIKE "%' . $this->matNomeId . '%")
		             OR (tbservidor.idfuncional LIKE "%' . $this->matNomeId . '%")';

                if (!is_null($this->idServidorIdPessoa)) {
                    $select .= ' OR (tbservidor.idServidor = ' . $this->idServidorIdPessoa . ')
		                 OR (tbservidor.idPessoa = ' . $this->idServidorIdPessoa . ')';
                }

                $select .= ')';
            }
        }

        # Ordenação
        if ($this->ordenacao == "nome") {
            $select .= ' ORDER BY tbpessoa.nome';
        } else {
            $select .= ' ORDER BY 4, 2';
        }

        $pessoal = new Pessoal();
        $retorno = $pessoal->select($select);

        return $retorno;
    }

###########################################################

    public function get_servidoresNaoEntregaramAtivos() {

        # Pega os dados
        $select = 'SELECT tbservidor.idfuncional,
                          tbservidor.idServidor,
                          concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")) lotacao,';

        # Exibe o afastamento ou não
        if ($this->exibeAfastamento) {
            $select .= ' tbservidor.idServidor,';
        }

        # Exibe o e-mail ou não
        if ($this->exibeEmail) {
            $select .= ' concat(IFnull(tbpessoa.emailUenf,""),"<br/>",IFnull(tbpessoa.emailPessoal,"")),';
        }

        $select .= ' tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         JOIN tbperfil USING (idPerfil)
                                         JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND tbperfil.tipo <> "Outros"
                     AND tbservidor.situacao = 1';
        # Lotacao
        if (!vazio($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        # Matrícula, nome ou id
        if (!is_null($this->matNomeId)) {
            if (is_numeric($this->matNomeId)) {
                $select .= ' AND ((';
                $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
            } else {

                # Verifica se tem espaços
                if (strpos($this->matNomeId, ' ') !== false) {
                    # Separa as palavras
                    $palavras = explode(' ', $this->matNomeId);

                    # Percorre as palavras
                    foreach ($palavras as $item) {
                        $select .= 'AND (tbpessoa.nome LIKE "%' . $item . '%")';
                    }
                } else {
                    $select .= ' AND (';
                    $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
                }
            }

            # Faz pesquisa na matricula e outros
            if (is_numeric($this->matNomeId)) {
                $select .= ' OR (tbservidor.matricula LIKE "%' . $this->matNomeId . '%")
		             OR (tbservidor.idfuncional LIKE "%' . $this->matNomeId . '%")';

                if (!is_null($this->idServidorIdPessoa)) {
                    $select .= ' OR (tbservidor.idServidor = ' . $this->idServidorIdPessoa . ')
		                 OR (tbservidor.idPessoa = ' . $this->idServidorIdPessoa . ')';
                }

                $select .= ')';
            }
        }

        $select .= ' AND tbservidor.idServidor NOT IN (SELECT tbsispatri.idServidor
                                              FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                              JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                             WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                               AND tbservidor.situacao = 1';

        # Lotacao
        if (!empty($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        # Ordenação
        if ($this->ordenacao == "nome") {
            $select .= ') ORDER BY tbpessoa.nome';
        } else {
            $select .= ') ORDER BY 3, tbpessoa.nome';
        }

        $pessoal = new Pessoal();
        $retorno = $pessoal->select($select);

        return $retorno;
    }

###########################################################

    public function get_servidoresNaoEntregaramAtivosFerias() {

        # Pega os dados
        $select = 'SELECT tbservidor.idfuncional,
                         tbservidor.idServidor,
                         concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")) lotacao,';

        # Exibe o afastamento ou não
        if ($this->exibeAfastamento) {
            $select .= ' tbservidor.idServidor,';
        }

        # Exibe o e-mail ou não
        if ($this->exibeEmail) {
            $select .= ' concat(IFnull(tbpessoa.emailUenf,""),"<br/>",IFnull(tbpessoa.emailPessoal,"")),';
        }

        $select .= ' tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                         JOIN tbferias ON (tbservidor.idServidor = tbferias.idServidor)
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND tbservidor.situacao = 1
                     AND CURDATE() >= dtInicial AND CURDATE() <= ADDDATE(dtInicial,numDias-1)
                     ';
        # Lotacao
        if (!empty($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        # Matrícula, nome ou id
        if (!is_null($this->matNomeId)) {
            if (is_numeric($this->matNomeId)) {
                $select .= ' AND ((';
                $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
            } else {

                # Verifica se tem espaços
                if (strpos($this->matNomeId, ' ') !== false) {
                    # Separa as palavras
                    $palavras = explode(' ', $this->matNomeId);

                    # Percorre as palavras
                    foreach ($palavras as $item) {
                        $select .= 'AND (tbpessoa.nome LIKE "%' . $item . '%")';
                    }
                } else {
                    $select .= ' AND (';
                    $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
                }
            }

            # Faz pesquisa na matricula e outros
            if (is_numeric($this->matNomeId)) {
                $select .= ' OR (tbservidor.matricula LIKE "%' . $this->matNomeId . '%")
		             OR (tbservidor.idfuncional LIKE "%' . $this->matNomeId . '%")';

                if (!is_null($this->idServidorIdPessoa)) {
                    $select .= ' OR (tbservidor.idServidor = ' . $this->idServidorIdPessoa . ')
		                 OR (tbservidor.idPessoa = ' . $this->idServidorIdPessoa . ')';
                }

                $select .= ')';
            }
        }

        $select .= ' AND tbservidor.idServidor NOT IN (SELECT tbsispatri.idServidor
                                              FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                              JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                             WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                               AND tbservidor.situacao = 1';

        # Lotacao
        if (!empty($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        # Ordenação
        if ($this->ordenacao == "nome") {
            $select .= ') ORDER BY tbpessoa.nome';
        } else {
            $select .= ') ORDER BY 3, tbpessoa.nome';
        }

        $pessoal = new Pessoal();
        $retorno = $pessoal->select($select);

        return $retorno;
    }

###########################################################

    public function get_servidoresNaoEntregaramAtivosLicPremio() {

        # Pega os dados
        $select = 'SELECT tbservidor.idfuncional,
                         tbservidor.idServidor,
                         concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")) lotacao,';

        # Exibe o afastamento ou não
        if ($this->exibeAfastamento) {
            $select .= ' tbservidor.idServidor,';
        }

        # Exibe o e-mail ou não
        if ($this->exibeEmail) {
            $select .= ' concat(IFnull(tbpessoa.emailUenf,""),"<br/>",IFnull(tbpessoa.emailPessoal,"")),';
        }

        $select .= ' tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                         JOIN tblicencapremio ON (tbservidor.idServidor = tblicencapremio.idServidor)
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND tbservidor.situacao = 1
                     AND CURDATE() >= dtInicial AND CURDATE() <= ADDDATE(dtInicial,numDias-1)
                     ';
        # Lotacao
        if (!empty($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        # Matrícula, nome ou id
        if (!is_null($this->matNomeId)) {
            if (is_numeric($this->matNomeId)) {
                $select .= ' AND ((';
                $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
            } else {

                # Verifica se tem espaços
                if (strpos($this->matNomeId, ' ') !== false) {
                    # Separa as palavras
                    $palavras = explode(' ', $this->matNomeId);

                    # Percorre as palavras
                    foreach ($palavras as $item) {
                        $select .= 'AND (tbpessoa.nome LIKE "%' . $item . '%")';
                    }
                } else {
                    $select .= ' AND (';
                    $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
                }
            }

            # Faz pesquisa na matricula e outros
            if (is_numeric($this->matNomeId)) {
                $select .= ' OR (tbservidor.matricula LIKE "%' . $this->matNomeId . '%")
		             OR (tbservidor.idfuncional LIKE "%' . $this->matNomeId . '%")';

                if (!is_null($this->idServidorIdPessoa)) {
                    $select .= ' OR (tbservidor.idServidor = ' . $this->idServidorIdPessoa . ')
		                 OR (tbservidor.idPessoa = ' . $this->idServidorIdPessoa . ')';
                }

                $select .= ')';
            }
        }

        $select .= ' AND tbservidor.idServidor NOT IN (SELECT tbsispatri.idServidor
                                              FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                              JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                             WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                               AND tbservidor.situacao = 1';

        # Lotacao
        if (!empty($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        # Ordenação
        if ($this->ordenacao == "nome") {
            $select .= ') ORDER BY tbpessoa.nome';
        } else {
            $select .= ') ORDER BY 3, tbpessoa.nome';
        }

        $pessoal = new Pessoal();
        $retorno = $pessoal->select($select);

        return $retorno;
    }

###########################################################

    public function get_servidoresNaoEntregaramAtivosLicMedica($exibeEmail = true) {

        # Pega os dados
        $select = 'SELECT tbservidor.idfuncional,
                         tbservidor.idServidor,
                         concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")) lotacao,';

        # Exibe o afastamento ou não
        if ($this->exibeAfastamento) {
            $select .= ' tbservidor.idServidor,';
        }

        # Exibe o e-mail ou não
        if ($this->exibeEmail) {
            $select .= ' concat(IFnull(tbpessoa.emailUenf,""),"<br/>",IFnull(tbpessoa.emailPessoal,"")),';
        }

        $select .= ' tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                         JOIN tblicenca ON (tbservidor.idServidor = tblicenca.idServidor)
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND tbservidor.situacao = 1
                     AND tblicenca.dtInicial = (select max(dtInicial) from tblicenca where tblicenca.idServidor = tbservidor.idServidor)
                     AND (CURDATE() >= dtInicial AND CURDATE() <= ADDDATE(dtInicial,numDias-1) OR alta <> 1)
                     AND (idTpLicenca = 1 OR idTpLicenca = 30)
                     ';
        # Lotacao
        if (!empty($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        # Matrícula, nome ou id
        if (!is_null($this->matNomeId)) {
            if (is_numeric($this->matNomeId)) {
                $select .= ' AND ((';
                $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
            } else {

                # Verifica se tem espaços
                if (strpos($this->matNomeId, ' ') !== false) {
                    # Separa as palavras
                    $palavras = explode(' ', $this->matNomeId);

                    # Percorre as palavras
                    foreach ($palavras as $item) {
                        $select .= 'AND (tbpessoa.nome LIKE "%' . $item . '%")';
                    }
                } else {
                    $select .= ' AND (';
                    $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
                }
            }

            # Faz pesquisa na matricula e outros
            if (is_numeric($this->matNomeId)) {
                $select .= ' OR (tbservidor.matricula LIKE "%' . $this->matNomeId . '%")
		             OR (tbservidor.idfuncional LIKE "%' . $this->matNomeId . '%")';

                if (!is_null($this->idServidorIdPessoa)) {
                    $select .= ' OR (tbservidor.idServidor = ' . $this->idServidorIdPessoa . ')
		                 OR (tbservidor.idPessoa = ' . $this->idServidorIdPessoa . ')';
                }

                $select .= ')';
            }
        }

        $select .= ' AND tbservidor.idServidor NOT IN (SELECT tbsispatri.idServidor
                                              FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                              JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                             WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                               AND tbservidor.situacao = 1';

        # Lotacao
        if (!empty($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        # Ordenação
        if ($this->ordenacao == "nome") {
            $select .= ') ORDER BY tbpessoa.nome';
        } else {
            $select .= ') ORDER BY 3, tbpessoa.nome';
        }

        $pessoal = new Pessoal();
        $retorno = $pessoal->select($select);

        return $retorno;
    }

###########################################################

    public function get_servidoresNaoEntregaramAtivosTrabalhando($exibeEmail = true) {

        # Pega os dados
        $select = 'SELECT tbservidor.idfuncional,
                         tbservidor.idServidor,
                         concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")) lotacao,';

        # Exibe o afastamento ou não
        if ($this->exibeAfastamento) {
            $select .= ' tbservidor.idServidor,';
        }

        # Exibe o e-mail ou não
        if ($this->exibeEmail) {
            $select .= ' concat(IFnull(tbpessoa.emailUenf,""),"<br/>",IFnull(tbpessoa.emailPessoal,"")),';
        }

        $select .= ' tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                        LEFT JOIN tbferias ON (tbservidor.idServidor = tbferias.idServidor)
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND tbservidor.situacao = 1
                     AND (CURDATE() < dtInicial OR CURDATE() > ADDDATE(dtInicial,numDias-1))
                     ';
        # Lotacao
        if (!empty($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        # Matrícula, nome ou id
        if (!is_null($this->matNomeId)) {
            if (is_numeric($this->matNomeId)) {
                $select .= ' AND ((';
                $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
            } else {

                # Verifica se tem espaços
                if (strpos($this->matNomeId, ' ') !== false) {
                    # Separa as palavras
                    $palavras = explode(' ', $this->matNomeId);

                    # Percorre as palavras
                    foreach ($palavras as $item) {
                        $select .= 'AND (tbpessoa.nome LIKE "%' . $item . '%")';
                    }
                } else {
                    $select .= ' AND (';
                    $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
                }
            }

            # Faz pesquisa na matricula e outros
            if (is_numeric($this->matNomeId)) {
                $select .= ' OR (tbservidor.matricula LIKE "%' . $this->matNomeId . '%")
		             OR (tbservidor.idfuncional LIKE "%' . $this->matNomeId . '%")';

                if (!is_null($this->idServidorIdPessoa)) {
                    $select .= ' OR (tbservidor.idServidor = ' . $this->idServidorIdPessoa . ')
		                 OR (tbservidor.idPessoa = ' . $this->idServidorIdPessoa . ')';
                }

                $select .= ')';
            }
        }

        $select .= ' AND tbservidor.idServidor NOT IN (SELECT tbsispatri.idServidor
                                              FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                              JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                             WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                               AND tbservidor.situacao = 1';

        # Lotacao
        if (!empty($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        # Ordenação
        if ($this->ordenacao == "nome") {
            $select .= ') ORDER BY tbpessoa.nome';
        } else {
            $select .= ') ORDER BY 3, tbpessoa.nome';
        }

        $pessoal = new Pessoal();
        $retorno = $pessoal->select($select);

        return $retorno;
    }

###########################################################

    public function get_servidoresEntregaramNaoAtivos() {

        # Pega os dados
        $select = 'SELECT tbservidor.idfuncional,
                         tbpessoa.nome,
                         tbservidor.idServidor,
                         concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")) lotacao,
                         tbservidor.idServidor
                    FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                         JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                   AND tbservidor.situacao <> 1';

        # Lotacao
        if (!empty($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        # Matrícula, nome ou id
        if (!is_null($this->matNomeId)) {
            if (is_numeric($this->matNomeId)) {
                $select .= ' AND ((';
                $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
            } else {

                # Verifica se tem espaços
                if (strpos($this->matNomeId, ' ') !== false) {
                    # Separa as palavras
                    $palavras = explode(' ', $this->matNomeId);

                    # Percorre as palavras
                    foreach ($palavras as $item) {
                        $select .= 'AND (tbpessoa.nome LIKE "%' . $item . '%")';
                    }
                } else {
                    $select .= ' AND (';
                    $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
                }
            }

            # Faz pesquisa na matricula e outros
            if (is_numeric($this->matNomeId)) {
                $select .= ' OR (tbservidor.matricula LIKE "%' . $this->matNomeId . '%")
		             OR (tbservidor.idfuncional LIKE "%' . $this->matNomeId . '%")';

                if (!is_null($this->idServidorIdPessoa)) {
                    $select .= ' OR (tbservidor.idServidor = ' . $this->idServidorIdPessoa . ')
		                 OR (tbservidor.idPessoa = ' . $this->idServidorIdPessoa . ')';
                }

                $select .= ')';
            }
        }

        # Ordenação
        if ($this->ordenacao == "nome") {
            $select .= ' ORDER BY tbpessoa.nome';
        } else {
            $select .= ' ORDER BY 4, 2';
        }

        $pessoal = new Pessoal();
        $retorno = $pessoal->select($select);

        return $retorno;
    }

###########################################################

    public function get_numServidoresAtivos() {

        # Pega os dados
        $select = 'SELECT tbservidor.idfuncional
                    FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                         JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                         JOIN tbperfil USING (idPerfil)
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                   AND tbperfil.tipo <> "Outros" 
                   AND tbservidor.situacao = 1';

        # Lotacao
        if (!empty($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        $pessoal = new Pessoal();
        $retorno = $pessoal->count($select);

        return $retorno;
    }

###########################################################

    public function get_numServidoresNaoAtivos() {

        # Pega os dados
        $select = 'SELECT tbservidor.idfuncional
                    FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                         JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                   AND tbservidor.situacao <> 1';

        # Lotacao
        if (!empty($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        $pessoal = new Pessoal();
        $retorno = $pessoal->count($select);

        return $retorno;
    }

###########################################################

    public function get_servidoresRelatorio() {

        # Pega os dados
        $select = 'SELECT tbservidor.idfuncional,
                         tbpessoa.nome,
                         concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND tbservidor.situacao = 1';
        # Lotacao
        if (!empty($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        # Matrícula, nome ou id
        if (!is_null($this->matNomeId)) {
            if (is_numeric($this->matNomeId)) {
                $select .= ' AND ((';
                $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
            } else {

                # Verifica se tem espaços
                if (strpos($this->matNomeId, ' ') !== false) {
                    # Separa as palavras
                    $palavras = explode(' ', $this->matNomeId);

                    # Percorre as palavras
                    foreach ($palavras as $item) {
                        $select .= 'AND (tbpessoa.nome LIKE "%' . $item . '%")';
                    }
                } else {
                    $select .= ' AND (';
                    $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
                }
            }

            # Faz pesquisa na matricula e outros
            if (is_numeric($this->matNomeId)) {
                $select .= ' OR (tbservidor.matricula LIKE "%' . $this->matNomeId . '%")
		             OR (tbservidor.idfuncional LIKE "%' . $this->matNomeId . '%")';

                if (!is_null($this->idServidorIdPessoa)) {
                    $select .= ' OR (tbservidor.idServidor = ' . $this->idServidorIdPessoa . ')
		                 OR (tbservidor.idPessoa = ' . $this->idServidorIdPessoa . ')';
                }

                $select .= ')';
            }
        }

        $select .= ' AND tbservidor.idServidor NOT IN (SELECT tbsispatri.idServidor
                                              FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                              JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                             WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                               AND tbservidor.situacao = 1';

        # Lotacao
        if (!vazio($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        $select .= ') ORDER BY 3,2';

        $pessoal = new Pessoal();
        return $pessoal->select($select);
    }

###########################################################

    public function get_numProblemas() {

        # Pega os dados
        $select = 'SELECT idsispatri
                     FROM tbsispatri 
                    WHERE idServidor Is NULL';

        $pessoal = new Pessoal();
        return $pessoal->count($select);
    }

###########################################################

    public function exibeProblemas() {

        # Pega os dados
        $select = 'SELECT cpf,
                          obs,
                          idSispatri
                     FROM tbsispatri 
                    WHERE idServidor Is NULL';

        $pessoal = new Pessoal();
        $array = $pessoal->select($select);

        # callout("Problema na Importação !!! Veja abaixo os problemas encontrados:", "alert");

        $tabela = new Tabela();
        $tabela->set_titulo("Problemas na Importação !!! Veja abaixo os problemas encontrados:");
        $tabela->set_conteudo($array);
        $tabela->set_label(array("CPF", "Outras informações"));
        $tabela->set_align(array("center", "left"));
        $tabela->set_width(array(20, 80));
        $tabela->set_excluir("?fase=excluir");
        $tabela->set_idCampo("idSispatri");
        $tabela->show();
    }

###########################################################

    public function exibeResumo() {

        # Servidores ativos que Entregaram o sispatri
        $numSispatriAtivos = $this->get_numServidoresAtivos();

        # Servidores no total
        $pessoal = new Pessoal();
        $numServidores = $pessoal->get_numServidoresAtivos($this->lotacao);

        $array = array(
            array("Entregaram o Sispatri", $numSispatriAtivos),
            array("Não Entregaram", $numServidores - $numSispatriAtivos)
        );

        tituloTable("Servidores Ativos");
        $chart = new Chart("Pie", $array);
        $chart->set_idDiv("cargo");
        $chart->set_legend(false);
        $chart->set_tamanho($largura = 300, $altura = 300);
        $chart->show();

        $tabela = new Tabela();
        #$tabela->set_titulo("Servidores Ativos");
        $tabela->set_subtitulo($pessoal->get_nomeLotacao($this->lotacao));
        $tabela->set_conteudo($array);
        $tabela->set_label(["Descrição", "Servidores"]);
        $tabela->set_align(["left", "center"]);
        $tabela->set_colunaSomatorio(1);
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

###########################################################

    public function exibeResumoPorCargoEntregaram() {

        # Servidores ativos que Entregaram o sispatri
        $numSispatriAtivos = $this->get_numServidoresAtivos();

        # Servidores no total
        $pessoal = new Pessoal();

        # Geral - Por Cargo
        $select = 'SELECT tbtipocargo.sigla, count(tbsispatri.idServidor) as jj
                                FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                                LEFT JOIN tbcargo USING (idCargo)
                                                LEFT JOIN tbtipocargo USING (idTipoCargo)
                                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                   AND tbservidor.situacao = 1';

        # Lotacao
        if (!vazio($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        $select .= ' GROUP BY tbtipocargo.cargo
                     ORDER BY tbtipocargo.cargo DESC ';

        $servidores = $pessoal->select($select);
        $total = array_sum(array_column($servidores, "jj"));

        array_push($servidores, array('Total', $total));

        # Exemplo de tabela simples
        $tabela = new Tabela();
        $tabela->set_titulo("Entregaram");
        $tabela->set_subtitulo($pessoal->get_nomeLotacao($this->lotacao));
        $tabela->set_conteudo($servidores);
        $tabela->set_label(["Tipo do Cargo", "Servidores"]);
        $tabela->set_width([80, 20]);
        $tabela->set_align(["left", "center"]);
        $tabela->set_formatacaoCondicional(array(
            array('coluna' => 0,
                'valor' => "Total",
                'operador' => '=',
                'id' => 'estatisticaTotal')));
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

###########################################################

    public function exibeResumoPorCargoNaoEntregaram() {

        # Servidores ativos que Entregaram o sispatri
        $numSispatriAtivos = $this->get_numServidoresAtivos();

        # Classe
        $pessoal = new Pessoal();

        # Geral - Por Cargo
        $select = 'SELECT tbtipocargo.sigla, count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT JOIN tbcargo USING (idCargo)
                                                LEFT JOIN tbtipocargo USING (idTipoCargo)
                                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                                     JOIN tbperfil USING (idPerfil)
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                   AND tbperfil.tipo <> "Outros"
                   AND tbservidor.situacao = 1';

        # Lotacao
        if (!vazio($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        $select .= ' AND tbservidor.idServidor NOT IN (SELECT tbsispatri.idServidor
                                              FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                              JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                             WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                               AND tbservidor.situacao = 1';

        # Lotacao
        if (!vazio($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }


        $select .= ') GROUP BY tbtipocargo.cargo
                     ORDER BY tbtipocargo.cargo DESC ';

        $servidores = $pessoal->select($select);
        $total = array_sum(array_column($servidores, "jj"));

        array_push($servidores, array('Total', $total));

        # Exemplo de tabela simples
        $tabela = new Tabela();
        $tabela->set_titulo("NÃO Entregaram");
        $tabela->set_subtitulo($pessoal->get_nomeLotacao($this->lotacao));
        $tabela->set_conteudo($servidores);
        $tabela->set_label(["Tipo do Cargo", "Servidores"]);
        $tabela->set_width([80, 20]);
        $tabela->set_align(["left", "center"]);
        $tabela->set_formatacaoCondicional(array(
            array('coluna' => 0,
                'valor' => "Total",
                'operador' => '=',
                'id' => 'estatisticaTotal')));
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

###########################################################

    public function exibeServidoresEntregaramAtivos() {

        # Classe
        $pessoal = new Pessoal();

        $result = $this->get_servidoresEntregaramAtivos();

        $tabela = new Tabela();
        $tabela->set_titulo('Servidores Ativos que ENTREGARAM a Declaração do Sispatri');
        $tabela->set_subtitulo($pessoal->get_nomeLotacao($this->lotacao));
        $tabela->set_label(["IdFuncional", "Nome", "Cargo", "Lotação", "Situação"]);
        $tabela->set_conteudo($result);
        $tabela->set_align(["center", "left", "left", "left"]);
        $tabela->set_classe([null, null, "pessoal"]);
        $tabela->set_metodo([null, null, "get_Cargo"]);
        $tabela->set_funcao([null, null, null, null, "get_situacao"]);

        $tabela->set_idCampo('idServidor');
        $tabela->set_editar('?fase=editaServidor');
        $tabela->show();
    }

###########################################################

    public function exibeServidoresEntregaramInativos() {

        if ($this->get_numServidoresNaoAtivos() > 0) {

            # Classe
            $pessoal = new Pessoal();

            $result = $this->get_servidoresEntregaramNaoAtivos();

            $tabela = new Tabela();
            $tabela->set_titulo('Servidores Inativos que Entregaram a Declaração do Sispatri');
            $tabela->set_subtitulo($pessoal->get_nomeLotacao($this->lotacao));
            $tabela->set_label(["IdFuncional", "Nome", "Cargo", "Lotação", "Situação"]);
            $tabela->set_conteudo($result);
            $tabela->set_align(["center", "left", "left", "left"]);
            $tabela->set_classe([null, null, "pessoal", null, "pessoal"]);
            $tabela->set_metodo([null, null, "get_Cargo", null, "get_situacao"]);

            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor');

            $tabela->show();
        }
    }

###########################################################

    public function exibeServidoresNaoEntregaramAtivos() {

        # Classe
        $pessoal = new Pessoal();

        $result = $this->get_servidoresNaoEntregaramAtivos();

        $tabela = new Tabela();
        $tabela->set_titulo('Servidores Ativos que NÃO Entregaram a Declaração do Sispatri');
        $tabela->set_subtitulo($pessoal->get_nomeLotacao($this->lotacao));
        $tabela->set_classe([null, "pessoal"]);
        $tabela->set_metodo([null, "get_nomeECargo"]);
        $tabela->set_idCampo('idServidor');
        $tabela->set_editar('?fase=editaServidor');
        $tabela->set_conteudo($result);

        $label = ["IdFuncional", "Servidor", "Lotação"];
        $align = ["center", "left", "left"];
        $funcao = [null, null, null];
        $width = [10, 30, 20];

        # Exibe o afastamento ou não
        if ($this->exibeAfastamento) {
            array_push($label, "Afastamentos");
            array_push($align, "left");
            array_push($funcao, "exibeAfastamentoAtual");
            array_push($width, 20);
        }

        # Exibe o e-mail ou não
        if ($this->exibeEmail) {
            array_push($label, "E-mail");
            array_push($align, null);
            array_push($funcao, null);
            array_push($width, 10);
        }

        array_push($label, "Situação");
        array_push($funcao, "get_situacao");
        array_push($width, 10);

        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_funcao($funcao);
        $tabela->set_width($width);

        $tabela->show();
    }

###########################################################

    public function exibeServidoresNaoEntregaramAtivosFerias() {

        # Classe
        $pessoal = new Pessoal();

        $result = $this->get_servidoresNaoEntregaramAtivosFerias();

        $tabela = new Tabela();
        $tabela->set_titulo('Servidores Ativos que NÃO Entregaram a Declaração do Sispatri');
        $tabela->set_subtitulo($pessoal->get_nomeLotacao($this->lotacao));
        $tabela->set_classe([null, "pessoal"]);
        $tabela->set_metodo([null, "get_nomeECargo"]);
        $tabela->set_idCampo('idServidor');
        $tabela->set_editar('?fase=editaServidor');
        $tabela->set_conteudo($result);

        $label = ["IdFuncional", "Servidor", "Lotação"];
        $align = ["center", "left", "left"];
        $funcao = [null, null, null];
        $width = [10, 30, 20];

        # Exibe o afastamento ou não
        if ($this->exibeAfastamento) {
            array_push($label, "Afastamentos");
            array_push($align, "left");
            array_push($funcao, "exibeAfastamentoAtual");
            array_push($width, 20);
        }

        # Exibe o e-mail ou não
        if ($this->exibeEmail) {
            array_push($label, "E-mail");
            array_push($align, null);
            array_push($funcao, null);
            array_push($width, 10);
        }

        array_push($label, "Situação");
        array_push($funcao, "get_situacao");
        array_push($width, 10);

        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_funcao($funcao);
        $tabela->set_width($width);

        $tabela->show();
    }

###########################################################

    public function exibeServidoresNaoEntregaramAtivosLicPremio() {

        # Classe
        $pessoal = new Pessoal();

        $result = $this->get_servidoresNaoEntregaramAtivosLicPremio();

        $tabela = new Tabela();
        $tabela->set_titulo('Servidores Ativos que NÃO Entregaram a Declaração do Sispatri');
        $tabela->set_subtitulo($pessoal->get_nomeLotacao($this->lotacao));
        $tabela->set_classe([null, "pessoal"]);
        $tabela->set_metodo([null, "get_nomeECargo"]);
        $tabela->set_idCampo('idServidor');
        $tabela->set_editar('?fase=editaServidor');
        $tabela->set_conteudo($result);

        $label = ["IdFuncional", "Servidor", "Lotação"];
        $align = ["center", "left", "left"];
        $funcao = [null, null, null];
        $width = [10, 30, 20];

        # Exibe o afastamento ou não
        if ($this->exibeAfastamento) {
            array_push($label, "Afastamentos");
            array_push($align, "left");
            array_push($funcao, "exibeAfastamentoAtual");
            array_push($width, 20);
        }

        # Exibe o e-mail ou não
        if ($this->exibeEmail) {
            array_push($label, "E-mail");
            array_push($align, null);
            array_push($funcao, null);
            array_push($width, 10);
        }

        array_push($label, "Situação");
        array_push($funcao, "get_situacao");
        array_push($width, 10);

        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_funcao($funcao);
        $tabela->set_width($width);

        $tabela->show();
    }

###########################################################

    public function exibeServidoresNaoEntregaramAtivosLicMedica() {

        # Classe
        $pessoal = new Pessoal();

        $result = $this->get_servidoresNaoEntregaramAtivosLicMedica();

        $tabela = new Tabela();
        $tabela->set_titulo('Servidores Ativos que NÃO Entregaram a Declaração do Sispatri');
        $tabela->set_subtitulo($pessoal->get_nomeLotacao($this->lotacao));
        $tabela->set_classe([null, "pessoal"]);
        $tabela->set_metodo([null, "get_nomeECargo"]);
        $tabela->set_idCampo('idServidor');
        $tabela->set_editar('?fase=editaServidor');
        $tabela->set_conteudo($result);

        $label = ["IdFuncional", "Servidor", "Lotação"];
        $align = ["center", "left", "left"];
        $funcao = [null, null, null];
        $width = [10, 30, 20];

        # Exibe o afastamento ou não
        if ($this->exibeAfastamento) {
            array_push($label, "Afastamentos");
            array_push($align, "left");
            array_push($funcao, "exibeAfastamentoAtual");
            array_push($width, 20);
        }

        # Exibe o e-mail ou não
        if ($this->exibeEmail) {
            array_push($label, "E-mail");
            array_push($align, null);
            array_push($funcao, null);
            array_push($width, 10);
        }

        array_push($label, "Situação");
        array_push($funcao, "get_situacao");
        array_push($width, 10);

        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_funcao($funcao);
        $tabela->set_width($width);

        $tabela->show();
    }

###########################################################

    /**
     * Método get_textoCi
     * 
     * Método que exibe o conteúdo de uma variável de configuração
     * 
     * @param	string	$var	-> o nome da variável
     */
    public function get_textoCi() {
        $select = 'SELECT textoCi
                     FROM tbsispatriconfig
                    WHERE idSispatriConfig = 1';
        $pessoal = new Pessoal();
        $valor = $pessoal->select($select, false);
        if (empty($valor[0])) {
            return null;
        } else {
            return $valor[0];
        }
    }

    ###########################################################

    /**
     * Método set_textoCi
     * 
     * Método que grava um conteúdo em uma variável de configuração
     * 
     * @param	string	$var	-> o nome da variável
     */
    public function set_textoCi($textoCi) {
        #$textoCi = retiraAspas($textoCi);
        $pessoal = new Pessoal();
        $pessoal->set_tabela('tbsispatriconfig');
        $pessoal->set_idCampo('idSispatriConfig');
        $pessoal->gravar(['textoCi'], [$textoCi], 1);
    }

    ###########################################################

    /**
     * Método exibeDataUltimaImportacao
     * 
     * Método exibe a data da última importação
     */
    public function exibeDataUltimaImportacao() {

        # Começa o painel
        $painel = new Callout("warning");
        $painel->abre();

        $intra = new Intra();
        p("Última Importação:", "pdataImportacaoSispatriTexto");
        p(trataNulo($intra->get_variavel('dataUltimaImportacao')), "pdataImportacaoSispatriValor");
        p(trataNulo("Feita pelo usuário: " . $intra->get_variavel('usuarioUltimaImportacao')), "pdataImportacaoSispatriTexto");

        $painel->fecha();
    }

    ###########################################################

    /**
     * Método exibeEmails
     * 
     * Método exibe uma relação de e-mail para serem copiados e colados 
     * 
     * @param	integer	$tipo	-> 1 - e-mail institucional | 2 - e-mail pessoal
     */
    public function exibeEmails($tipo = 1) {
        # Pega os dados
        if ($tipo == 1) {
            $select = 'SELECT tbpessoa.emailUenf ';
        } else {
            $select = 'SELECT tbpessoa.emailPessoal ';
        }
        $select .= ' FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                          JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                          JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                          JOIN tbperfil USING (idPerfil) 
                    WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                      AND tbservidor.situacao = 1
                      AND tbperfil.tipo <> "Outros"';
        # Lotacao
        if (!vazio($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        $select .= ' AND tbservidor.idServidor NOT IN (SELECT tbsispatri.idServidor
                                              FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                              JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                             WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                               AND tbservidor.situacao = 1';

        # Lotacao
        if (!vazio($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        # Matrícula, nome ou id
        if (!is_null($this->matNomeId)) {
            if (is_numeric($this->matNomeId)) {
                $select .= ' AND ((';
                $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
            } else {

                # Verifica se tem espaços
                if (strpos($this->matNomeId, ' ') !== false) {
                    # Separa as palavras
                    $palavras = explode(' ', $this->matNomeId);

                    # Percorre as palavras
                    foreach ($palavras as $item) {
                        $select .= 'AND (tbpessoa.nome LIKE "%' . $item . '%")';
                    }
                } else {
                    $select .= ' AND (';
                    $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
                }
            }

            # Faz pesquisa na matricula e outros
            if (is_numeric($this->matNomeId)) {
                $select .= ' OR (tbservidor.matricula LIKE "%' . $this->matNomeId . '%")
		             OR (tbservidor.idfuncional LIKE "%' . $this->matNomeId . '%")';

                if (!is_null($this->idServidorIdPessoa)) {
                    $select .= ' OR (tbservidor.idServidor = ' . $this->idServidorIdPessoa . ')
		                 OR (tbservidor.idPessoa = ' . $this->idServidorIdPessoa . ')';
                }

                $select .= ')';
            }
        }

        $select .= ') ORDER BY tbpessoa.nome';

        $pessoal = new Pessoal();
        $retorno = $pessoal->select($select);

        foreach ($retorno as $item) {
            if (!empty($item[0])) {
                echo $item[0] . ", ";
            }
        }
    }

    ###########################################################

    /**
     * Método exibeEmailsFerias
     * 
     * Método exibe uma relação de e-mail para serem copiados e colados 
     */
    public function exibeEmailsFerias($tipo = 1) {
        # Pega os dados
        if ($tipo == 1) {
            $select = 'SELECT tbpessoa.emailUenf ';
        } else {
            $select = 'SELECT tbpessoa.emailPessoal ';
        }
        $select .= ' FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                         JOIN tbferias ON (tbservidor.idServidor = tbferias.idServidor)
                                         JOIN tbperfil USING (idPerfil) 
                    WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                      AND tbservidor.situacao = 1
                      AND tbperfil.tipo <> "Outros"
                     AND CURDATE() >= dtInicial AND CURDATE() <= ADDDATE(dtInicial,numDias-1)';
        # Lotacao
        if (!vazio($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        # Matrícula, nome ou id
        if (!is_null($this->matNomeId)) {
            if (is_numeric($this->matNomeId)) {
                $select .= ' AND ((';
                $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
            } else {

                # Verifica se tem espaços
                if (strpos($this->matNomeId, ' ') !== false) {
                    # Separa as palavras
                    $palavras = explode(' ', $this->matNomeId);

                    # Percorre as palavras
                    foreach ($palavras as $item) {
                        $select .= 'AND (tbpessoa.nome LIKE "%' . $item . '%")';
                    }
                } else {
                    $select .= ' AND (';
                    $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
                }
            }

            # Faz pesquisa na matricula e outros
            if (is_numeric($this->matNomeId)) {
                $select .= ' OR (tbservidor.matricula LIKE "%' . $this->matNomeId . '%")
		             OR (tbservidor.idfuncional LIKE "%' . $this->matNomeId . '%")';

                if (!is_null($this->idServidorIdPessoa)) {
                    $select .= ' OR (tbservidor.idServidor = ' . $this->idServidorIdPessoa . ')
		                 OR (tbservidor.idPessoa = ' . $this->idServidorIdPessoa . ')';
                }

                $select .= ')';
            }
        }

        $select .= ' AND tbservidor.idServidor NOT IN (SELECT tbsispatri.idServidor
                                              FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                              JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                             WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                               AND tbservidor.situacao = 1';

        # Lotacao
        if (!vazio($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        $select .= ') ORDER BY tbpessoa.nome';

        $pessoal = new Pessoal();
        $retorno = $pessoal->select($select);

        foreach ($retorno as $item) {
            if (!empty($item[0])) {
                echo $item[0] . ", ";
            }
        }
    }

    ###########################################################

    /**
     * Método exibeEmailsLicMedica
     * 
     * Método exibe uma relação de e-mail para serem copiados e colados 
     */
    public function exibeEmailsLicMedica($tipo = 1) {
        # Pega os dados
        if ($tipo == 1) {
            $select = 'SELECT tbpessoa.emailUenf ';
        } else {
            $select = 'SELECT tbpessoa.emailPessoal ';
        }
        $select .= '  tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                         JOIN tblicenca ON (tbservidor.idServidor = tblicenca.idServidor)
                                         JOIN tbperfil USING (idPerfil) 
                    WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND tbservidor.situacao = 1
                     AND tbperfil.tipo <> "Outros"
                     AND tblicenca.dtInicial = (select max(dtInicial) from tblicenca where tblicenca.idServidor = tbservidor.idServidor)
                     AND (CURDATE() >= dtInicial AND CURDATE() <= ADDDATE(dtInicial,numDias-1) OR alta <> 1)
                     AND (idTpLicenca = 1 OR idTpLicenca = 30)';
        # Lotacao
        if (!vazio($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        # Matrícula, nome ou id
        if (!is_null($this->matNomeId)) {
            if (is_numeric($this->matNomeId)) {
                $select .= ' AND ((';
                $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
            } else {

                # Verifica se tem espaços
                if (strpos($this->matNomeId, ' ') !== false) {
                    # Separa as palavras
                    $palavras = explode(' ', $this->matNomeId);

                    # Percorre as palavras
                    foreach ($palavras as $item) {
                        $select .= 'AND (tbpessoa.nome LIKE "%' . $item . '%")';
                    }
                } else {
                    $select .= ' AND (';
                    $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
                }
            }

            # Faz pesquisa na matricula e outros
            if (is_numeric($this->matNomeId)) {
                $select .= ' OR (tbservidor.matricula LIKE "%' . $this->matNomeId . '%")
		             OR (tbservidor.idfuncional LIKE "%' . $this->matNomeId . '%")';

                if (!is_null($this->idServidorIdPessoa)) {
                    $select .= ' OR (tbservidor.idServidor = ' . $this->idServidorIdPessoa . ')
		                 OR (tbservidor.idPessoa = ' . $this->idServidorIdPessoa . ')';
                }

                $select .= ')';
            }
        }

        $select .= ' AND tbservidor.idServidor NOT IN (SELECT tbsispatri.idServidor
                                              FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                              JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                             WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                               AND tbservidor.situacao = 1';

        # Lotacao
        if (!vazio($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        $select .= ') ORDER BY tbpessoa.nome';

        $pessoal = new Pessoal();
        $retorno = $pessoal->select($select);

        foreach ($retorno as $item) {
            if (!empty($item[0])) {
                echo $item[0] . ", ";
            }
        }
    }

    ###########################################################

    /**
     * Método exibeEmailsLicPremio
     * 
     * Método exibe uma relação de e-mail para serem copiados e colados 
     */
    public function exibeEmailsLicPremio($tipo = 1) {
        # Pega os dados
        if ($tipo == 1) {
            $select = 'SELECT tbpessoa.emailUenf ';
        } else {
            $select = 'SELECT tbpessoa.emailPessoal ';
        }
        $select .= '  tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                         JOIN tblicencapremio ON (tbservidor.idServidor = tblicencapremio.idServidor)
                                         JOIN tbperfil USING (idPerfil) 
                    WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                      AND tbservidor.situacao = 1
                      AND tbperfil.tipo <> "Outros"
                     AND CURDATE() >= dtInicial AND CURDATE() <= ADDDATE(dtInicial,numDias-1)';
        # Lotacao
        if (!vazio($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        # Matrícula, nome ou id
        if (!is_null($this->matNomeId)) {
            if (is_numeric($this->matNomeId)) {
                $select .= ' AND ((';
                $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
            } else {

                # Verifica se tem espaços
                if (strpos($this->matNomeId, ' ') !== false) {
                    # Separa as palavras
                    $palavras = explode(' ', $this->matNomeId);

                    # Percorre as palavras
                    foreach ($palavras as $item) {
                        $select .= 'AND (tbpessoa.nome LIKE "%' . $item . '%")';
                    }
                } else {
                    $select .= ' AND (';
                    $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';
                }
            }

            # Faz pesquisa na matricula e outros
            if (is_numeric($this->matNomeId)) {
                $select .= ' OR (tbservidor.matricula LIKE "%' . $this->matNomeId . '%")
		             OR (tbservidor.idfuncional LIKE "%' . $this->matNomeId . '%")';

                if (!is_null($this->idServidorIdPessoa)) {
                    $select .= ' OR (tbservidor.idServidor = ' . $this->idServidorIdPessoa . ')
		                 OR (tbservidor.idPessoa = ' . $this->idServidorIdPessoa . ')';
                }

                $select .= ')';
            }
        }

        $select .= ' AND tbservidor.idServidor NOT IN (SELECT tbsispatri.idServidor
                                              FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                              JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                             WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                               AND tbservidor.situacao = 1';

        # Lotacao
        if (!vazio($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        $select .= ') ORDER BY tbpessoa.nome';

        $pessoal = new Pessoal();
        $retorno = $pessoal->select($select);

        foreach ($retorno as $item) {
            if (!empty($item[0])) {
                echo $item[0] . ", ";
            }
        }
    }

    ###########################################################
}
