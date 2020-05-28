<?php

class CargoComissao {
    /**
     * Abriga as várias rotina do Cadastro de cargo em Comissao
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ###########################################################

    /**
     * Método Construtor
     */
    public function __construct() {
        
    }

    ###########################################################

    function get_dados($idComissao) {

        /**
         * fornece a próxima tarefa a ser realizada
         */
        # Pega os dados
        $select = "SELECT *
                   FROM tbcomissao
                  WHERE idComissao = $idComissao";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, FALSE);

        return $dados;
    }

    ###########################################################

    function get_descricaoCargo($idComissao) {

        /**
         * fornece a próxima tarefa a ser realizada
         */
        # Pega os dados
        $select = "SELECT tbdescricaocomissao.descricao
                   FROM tbdescricaocomissao JOIN tbcomissao USING (idDescricaoComissao)
                  WHERE idComissao = $idComissao";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, FALSE);

        return $dados[0];
    }

    ###########################################################

    function get_cargoCompleto($idComissao) {

        /**
         * fornece a próxima tarefa a ser realizada
         */
        # Pega os dados
        $select = "SELECT tbtipocomissao.simbolo,
                        tbtipocomissao.descricao,
                        tbdescricaocomissao.descricao
                   FROM tbcomissao JOIN tbtipocomissao USING (idTipoComisso)
                                   JOIN descricaocomissao USING (idDescricaoComissao)
                  WHERE tbcomissao.idComissao = $idComissao";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, FALSE);

        $retorno = $dados[0] . " - " . $dados[1] . "<br/>" . $dados[2];
        return $retorno;
    }

    ###########################################################

    function get_numServidoresNomeados($idTipoCargo) {

        /**
         * 
         * Informa o número de servidores ativos nomeados para esse cargo
         * 
         */
        # Pega os dados
        $select = "SELECT tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbcomissao USING(idServidor)
                    WHERE tbcomissao.idTipoComissao = $idTipoCargo
                      AND situacao = 1
                      AND (tbcomissao.dtExo IS NULL OR CURDATE() < tbcomissao.dtExo)
                      AND (tbcomissao.tipo is NULL OR tbcomissao.tipo = 0 OR tbcomissao.tipo = 1)";  // Curioso bug... tbcomissao.tipo <> 2 não funcionou
        // devido a alguns valores nulos cadastrado no campo tipo
        $pessoal = new Pessoal();
        $dados = $pessoal->count($select);
        return $dados;
    }

    ###########################################################

    function get_numServidoresDesignados($idTipoCargo) {

        /**
         * 
         * Informa o número de servidores ativos nomeados para esse cargo
         * 
         */
        # Pega os dados
        $select = "SELECT tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbcomissao USING(idServidor)
                    WHERE tbcomissao.idTipoComissao = $idTipoCargo
                      AND situacao = 1
                      AND (tbcomissao.dtExo IS NULL OR CURDATE() < tbcomissao.dtExo)
                      AND tbcomissao.tipo = 2";

        $pessoal = new Pessoal();
        $dados = $pessoal->count($select);
        return $dados;
    }

    ###########################################################

    function get_numServidoresProTempore($idTipoCargo) {

        /**
         * 
         * Informa o número de servidores ativos nomeados para esse cargo
         * 
         */
        # Pega os dados
        $select = "SELECT tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbcomissao USING(idServidor)
                    WHERE tbcomissao.idTipoComissao = $idTipoCargo
                      AND situacao = 1
                      AND (tbcomissao.dtExo IS NULL OR CURDATE() < tbcomissao.dtExo)
                      AND tbcomissao.tipo = 1";

        $pessoal = new Pessoal();
        $dados = $pessoal->count($select);
        return $dados;
    }

    ###########################################################

    /**
     * Método get_vagas
     * 
     * Exibe o número de vagas em um determinado cargo em comissao
     */
    public function get_vagas($idTipoCargo) {
        $select = 'SELECT vagas                             
                     FROM tbtipocomissao 
                    WHERE idTipoComissao = ' . $idTipoCargo;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, FALSE);
        return $row[0];
    }

    ###########################################################

    /**
     * Método get_vagasDisponiveis
     * 
     * Exibe o número de vagas disponíveis em um determinado cargo em comissao
     */
    public function get_vagasDisponiveis($idTipoCargo) {

        $vagas = $this->get_vagas($idTipoCargo);
        $nomeados = $this->get_numServidoresNomeados($idTipoCargo);
        $dispoinivel = $vagas - $nomeados;

        return $dispoinivel;
    }

    ###########################################################

    function exibeResumo($idTipoCargo) {

        /**
         * Exibe um quadro com o resumo do tipo de cargo
         */
        # Pega os dados
        $dados = array();
        $vagas = $this->get_vagas($idTipoCargo);
        $nomeados = $this->get_numServidoresNomeados($idTipoCargo);
        $designados = $this->get_numServidoresDesignados($idTipoCargo);
        $proTempore = $this->get_numServidoresProTempore($idTipoCargo);
        $dispoinivel = $this->get_vagasDisponiveis($idTipoCargo);

        $simbolo = $this->get_simbolo($idTipoCargo);
        $valor = $this->get_valor($idTipoCargo);

        # Pega dados da Classe Pessoal
        $pessoal = new Pessoal();
        $nomeCargo = $pessoal->get_nomeCargoComissao($idTipoCargo);


        # Coloca no array
        $dados[] = array($nomeCargo, $simbolo, "R$ " . formataMoeda($valor), $vagas, $nomeados, $dispoinivel, $proTempore, $designados);

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($dados);
        $tabela->set_label(array("Cargo", "Símbolo", "Valor", "Vagas", "Nomeados", "Disponíveis", "Pro Tempore", "Designados"));
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_align(array("center"));
        $tabela->set_titulo($nomeCargo);
        $tabela->set_formatacaoCondicional(array(array('coluna' => 5,
                'valor' => 0,
                'operador' => '<',
                'id' => "comissaoVagasNegativas"),
            array('coluna' => 5,
                'valor' => 0,
                'operador' => '=',
                'id' => "comissaoSemVagas"),
            array('coluna' => 5,
                'valor' => 0,
                'operador' => '>',
                'id' => "comissaoSemVagas")));
        $tabela->show();

        # Exibe alerta de nomeação a maios que vagas
        if ($nomeados > $vagas) {
            $painel = new Callout("warning");
            $painel->abre();

            p("ATENÇÂO !!!<br/>Existem mais servidores nomeados que vagas !!<br/>$vagas Vagas<br/>$nomeados Servidores Nomeados", "center");

            $painel->fecha();
        }
    }

    ###########################################################

    /**
     * Método get_simbolo
     * 
     * Exibe o símbolo de um determinado cargo em comissao
     */
    public function get_simbolo($idTipoCargo) {

        $select = 'SELECT simbolo                             
                     FROM tbtipocomissao 
                    WHERE idTipoComissao = ' . $idTipoCargo;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, FALSE);
        return $row[0];
    }

    ###########################################################

    /**
     * Método get_valor
     * 
     * Exibe o valor de um determinado cargo em comissao
     */
    public function get_valor($idTipoCargo) {

        $select = 'SELECT valsal                             
                     FROM tbtipocomissao 
                    WHERE idTipoComissao = ' . $idTipoCargo;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, FALSE);
        return $row[0];
    }

    ###########################################################

    function exibeBotaoDocumentos($idComissao) {

        /**
         * Exibe o botão de imprimir os documentos de uma solicitação de redução de carga horária específica
         * 
         * @obs Usada na tabela inicial do cadastro de redução
         */
        $menu = new MenuGrafico(3);

        # Ato de Nomeação
        $botao = new BotaoGrafico();
        $botao->set_url('?fase=atoNomeacao2&id=' . $idComissao);
        $botao->set_label("Ato de Nomeação");
        $botao->set_imagem(PASTA_FIGURAS . 'print.png', 20, 20);
        $botao->set_title("Imprime o Ato de Nomeação");
        $menu->add_item($botao);

        # Termo de Posse
        $botao = new BotaoGrafico();
        $botao->set_url('?fase=termoPosse&id=' . $idComissao);
        $botao->set_label("Termo de Posse");
        $botao->set_imagem(PASTA_FIGURAS . 'print.png', 20, 20);
        $botao->set_title("Imprime o Termo de Posse");
        $menu->add_item($botao);

        # Ato de Exoneração
        $botao = new BotaoGrafico();
        $botao->set_url('?fase=atoExoneracao&id=' . $idComissao);
        $botao->set_label("Ato de Exoneração");
        $botao->set_imagem(PASTA_FIGURAS . 'print.png', 20, 20);
        $botao->set_title("Imprime o Ato de Exoneração");
        $menu->add_item($botao);

        $menu->show();
    }

    ###########################################################

    /**
     * Método get_ocupanteAnterior
     * 
     * Exibe o valor de um determinado cargo em comissao
     */
    public function get_ocupanteAnterior($idComissao) {

        # Pega a descrição
        $dados = $this->get_dados($idComissao);
        $idDescricaoComissao = $dados['idDescricaoComissao'];

        # Pega os Servidores com a mesma descrição
        $select = "SELECT idServidor
                     FROM tbcomissao
                    WHERE idDescricaoComissao = $idDescricaoComissao 
                 ORDER BY dtNom desc
                    LIMIT 2";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select);
        $idServidor = $row[1][0];

        # Pega o nome desse Servidor
        $return = $pessoal->get_nome($idServidor);
        return $return;
    }

    ###########################################################

    /**
     * Método exibeNomeadosDescricao
     * 
     * Exibe uma tabela com os servidores nomeados nessa descrição desde a criação da universidade 
     */
    public function exibeNomeadosDescricao($idComissao) {

        # Pega a descrição
        $dados = $this->get_dados($idComissao);
        $idDescricaoComissao = $dados['idDescricaoComissao'];


        # Pega os Servidores com a mesma descrição
        $select = "SELECT tbpessoa.nome,
                          tbcomissao.dtNom,
                          tbcomissao.dtExo,
                          idComissao
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbcomissao USING(idServidor)
                                     LEFT JOIN tbdescricaocomissao USING (idDescricaoComissao)
                                          JOIN tbtipocomissao ON(tbcomissao.idTipoComissao=tbtipocomissao.idTipoComissao)
                   WHERE tbcomissao.idDescricaoComissao = $idDescricaoComissao
                ORDER BY tbdescricaocomissao.descricao, tbcomissao.dtNom desc";

        $pessoal = new Pessoal();
        $result = $pessoal->select($select);
        $label = array('Nome', 'Nomeação', 'Exoneração', 'Descrição');
        $align = array("left", "center", "center", "left");
        $function = array(NULL, "date_to_php", "date_to_php", "descricaoComissao");

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_titulo("Histórico de Servidores Nomeados Nesse Cargo");
        $tabela->set_align($align);
        $tabela->set_funcao($function);
        #$tabela->set_classe($classe);
        #$tabela->set_metodo($metodo);
        $tabela->set_formatacaoCondicional(array(array('coluna' => 2,
                'valor' => NULL,
                'operador' => '=',
                'id' => 'vigente')));
        $tabela->show();
    }

    ###########################################################        
}
