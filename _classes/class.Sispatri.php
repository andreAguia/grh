<?php

class Sispatri {

    /**
     * Abriga as várias rotina do COntrole Sispatri
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    private $lotacao = null;
    private $situacao = null;

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
        if (!vazio($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        $select .= ' ORDER BY 4,2';

        $pessoal = new Pessoal();
        $retorno = $pessoal->select($select);

        return $retorno;
    }

###########################################################

    public function get_servidoresNaoEntregaramAtivos() {

        # Pega os dados
        $select = 'SELECT tbservidor.idfuncional,
                         tbpessoa.nome,
                         tbservidor.idServidor,
                         concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")) lotacao,
                         tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
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

        $select .= ') ORDER BY 4,2';

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
        if (!vazio($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        $select .= ' ORDER BY 4,2';

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
        if (!vazio($this->lotacao)) {
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
        $select = 'SELECT cpf,obs,idSispatri
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
            array("Não Entregaram", $numServidores - $numSispatriAtivos),
            array("Total", $numServidores),
        );

        $tabela = new Tabela();
        $tabela->set_titulo("Servidores Ativos");
        $tabela->set_conteudo($array);
        $tabela->set_label(array("Descrição", "Servidores"));
        $tabela->set_align(array("left", "center"));
        $tabela->set_totalRegistro(false);
        $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                'valor' => "Total",
                'operador' => '=',
                'id' => 'estatisticaTotal')));
        $tabela->show();
    }

###########################################################

    public function exibeServidoresEntregaramAtivos() {

        $result = $this->get_servidoresEntregaramAtivos();

        $tabela = new Tabela();
        $tabela->set_titulo('Servidores Ativos que ENTREGARAM a Declaração do Sispatri');
        #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
        $tabela->set_label(array("IdFuncional", "Nome", "Cargo", "Lotação", "Situação"));
        $tabela->set_conteudo($result);
        $tabela->set_align(array("center", "left", "left", "left"));
        $tabela->set_classe(array(null, null, "pessoal"));
        $tabela->set_metodo(array(null, null, "get_Cargo"));
        $tabela->set_funcao(array(null, null, null, null, "get_situacao"));

        $tabela->set_idCampo('idServidor');
        $tabela->set_editar('?fase=editaServidor');
        $tabela->show();
    }

###########################################################

    public function exibeServidoresEntregaramInativos() {

        if ($this->get_numServidoresNaoAtivos() > 0) {

            $result = $this->get_servidoresEntregaramNaoAtivos();

            $tabela = new Tabela();
            $tabela->set_titulo('Servidores Inativos que Entregaram a Declaração do Sispatri');
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(array("IdFuncional", "Nome", "Cargo", "Lotação", "Situação"));
            $tabela->set_conteudo($result);
            $tabela->set_align(array("center", "left", "left", "left"));
            $tabela->set_classe(array(null, null, "pessoal", null, "pessoal"));
            $tabela->set_metodo(array(null, null, "get_Cargo", null, "get_situacao"));

            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor');

            $tabela->show();
        }
    }

###########################################################

    public function exibeServidoresNaoEntregaramAtivos() {

        $result = $this->get_servidoresNaoEntregaramAtivos();

        $tabela = new Tabela();
        $tabela->set_titulo('Servidores Ativos que NÃO Entregaram a Declaração do Sispatri');
        #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
        $tabela->set_label(array("IdFuncional", "Nome", "Cargo", "Lotação", "Situação"));
        $tabela->set_conteudo($result);
        $tabela->set_align(array("center", "left", "left", "left"));
        $tabela->set_classe(array(null, null, "pessoal"));
        $tabela->set_metodo(array(null, null, "get_Cargo"));
        $tabela->set_funcao(array(null, null, null, null, "get_situacao"));

        $tabela->set_idCampo('idServidor');
        $tabela->set_editar('?fase=editaServidor');
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
        $valor = $pessoal->select($select,false);
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
}
