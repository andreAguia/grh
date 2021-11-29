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
        $dados = $pessoal->select($select, false);

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
        $dados = $pessoal->select($select, false);

        if (empty($dados[0])) {
            return null;
        } else {
            return $dados[0];
        }
    }

    ###########################################################

    function exibeCargoCompleto($idComissao) {

        /**
         * fornece a próxima tarefa a ser realizada
         */
        # Pega os dados
        $select = "SELECT tbtipocomissao.simbolo,
                          tbtipocomissao.descricao,
                          tbdescricaocomissao.descricao,
                          tbcomissao.tipo
                     FROM tbcomissao LEFT JOIN tbtipocomissao USING (idTipoComissao)
                                     LEFT JOIN tbdescricaocomissao USING (idDescricaoComissao)
                    WHERE tbcomissao.idComissao = $idComissao";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        if (empty($dados[0])) {
            return null;
        } else {
            pLista(
                    "{$dados[0]} - {$dados[1]}",
                    $dados[2]
            );
        }

        # Informa o tipo
        switch ($dados['tipo']) {

            case 1:
                label("Pro Tempore");
                break;

            case 2:
                label("Designado");
                break;
        }
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
                      AND (tbcomissao.dtExo IS null OR CURDATE() < tbcomissao.dtExo)
                      AND (tbcomissao.tipo is null OR tbcomissao.tipo = 0 OR tbcomissao.tipo = 1)";  // Curioso bug... tbcomissao.tipo <> 2 não funcionou
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
                      AND (tbcomissao.dtExo IS null OR CURDATE() < tbcomissao.dtExo)
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
                      AND (tbcomissao.dtExo IS null OR CURDATE() < tbcomissao.dtExo)
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
        $row = $pessoal->select($select, false);
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
        $tabela->set_totalRegistro(false);
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
        $row = $pessoal->select($select, false);
        return $row[0];
    }

    ###########################################################

    /**
     * Método get_descricaoTipoCargo
     * 
     * Exibe a descrição de um determinado tipo de cargo em comissao
     */
    public function get_descricaoTipoCargo($idTipoCargo) {

        $select = 'SELECT descricao                             
                     FROM tbtipocomissao 
                    WHERE idTipoComissao = ' . $idTipoCargo;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);
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
        $row = $pessoal->select($select, false);
        return $row[0];
    }

    ###########################################################

    function exibeBotaoDocumentos($idComissao) {

        /**
         * Exibe o botão de imprimir os documentos de uma solicitação de redução de carga horária específica
         * 
         * @obs Usada na tabela inicial do cadastro de redução
         */
        $menu = new Menu("menuBeneficios");

        # Ato de Nomeação
        $menu->add_item('link', "\u{1F5A8} Ato de Nomeação", '?fase=atoNomeacao2&id=' . $idComissao, "Imprime o Ato de Nomeação");

        # Termo de Posse
        $menu->add_item('link', "\u{1F5A8} Termo de Posse", '?fase=termoPosse&id=' . $idComissao, "Imprime o Termo de Posse");

        # Ato de Exoneração
        $menu->add_item('link', "\u{1F5A8} Ato de Exoneração", '?fase=atoExoneracao&id=' . $idComissao, "Imprime o Ato de Exoneração");

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
        $function = array(null, "date_to_php", "date_to_php", "descricaoComissao");

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
                'valor' => null,
                'operador' => '=',
                'id' => 'vigente')));
        $tabela->show();
    }

    ###########################################################

    function exibeDadosNomeacao($idComissao) {

        /**
         * Exibe todos os dados de uma nomeação
         * Utilizado na tabela de histórico de cargo em comissão de um servidor
         */
        # Pega os dados
        $select = "SELECT dtNom,
                          dtAtoNom,
                          numProcNom,
                          dtPublicNom
                     FROM tbcomissao 
                    WHERE idComissao = {$idComissao}";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);
        $retorna = null;

        if (!empty($dados["dtNom"])) {
            $retorna .= "Nomeação: " . date_to_php($dados["dtNom"]);
            $retorna .= "<br/>";
        }

        if (!empty($dados["dtPublicNom"])) {
            $retorna .= "Publicação: " . date_to_php($dados["dtPublicNom"]);
            $retorna .= "<br/>";
        }

        if (!empty($dados["dtAtoNom"])) {
            $retorna .= "Ato do Reitor: " . date_to_php($dados["dtAtoNom"]);
            $retorna .= "<br/>";
        }

        if (!empty($dados["numProcNom"])) {
            $retorna .= "Processo: " . $dados["numProcNom"];
        }

        return $retorna;
    }

    ###########################################################

    function exibeDadosExoneracao($idComissao) {

        /**
         * Exibe todos os dados de uma nomeação
         * Utilizado na tabela de histórico de cargo em comissão de um servidor
         */
        # Pega os dados
        $select = "SELECT dtExo,
                          dtAtoExo,
                          numProcExo,
                          dtPublicExo
                     FROM tbcomissao 
                    WHERE idComissao = {$idComissao}";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);
        $retorna = null;

        if (!empty($dados["dtExo"])) {
            $retorna .= "Exoneração: " . date_to_php($dados["dtExo"]);
            $retorna .= "<br/>";
        }

        if (!empty($dados["dtPublicExo"])) {
            $retorna .= "Publicação: " . date_to_php($dados["dtPublicExo"]);
            $retorna .= "<br/>";
        }

        if (!empty($dados["dtAtoExo"])) {
            $retorna .= "Ato do Reitor: " . date_to_php($dados["dtAtoExo"]);
            $retorna .= "<br/>";
        }

        if (!empty($dados["numProcExo"])) {
            $retorna .= "Processo: " . $dados["numProcExo"];
        }

        return $retorna;
    }

    ###########################################################

    function exibeDadosVagas($idTipoCargo) {

        /**
         * Exibe todos os dados de vagas de um cargo
         */
        $vagas = $this->get_vagas($idTipoCargo);
        $nomeados = $this->get_numServidoresNomeados($idTipoCargo);
        $protempore = $this->get_numServidoresProTempore($idTipoCargo);
        $designado = $this->get_numServidoresDesignados($idTipoCargo);
        $disponiveis = $this->get_vagasDisponiveis($idTipoCargo);
        $simbolo = $this->get_simbolo($idTipoCargo);
        $descricao = $this->get_descricaoTipoCargo($idTipoCargo);

        echo "{$simbolo} - {$descricao}";
        br();
        echo "{$vagas} vaga(s) | {$nomeados} nomeado(s) | {$disponiveis} disponível(is)";
        br();
        if (!empty($designado)) {
            echo "{$designado} designado(s)";
        }
        if (!empty($protempore)) {
            if (!empty($designado)) {
                echo " | ";
            }
            echo "{$protempore} pro tempore";
        }
    }

    ###########################################################

    function exibeNomeadoVigente($idComissao) {

        /**
         * Exibe todos os dados de vagas de um cargo
         */
        $dados = $this->get_dados($idComissao);
        $idServidor = $dados["idServidor"];

        $pessoal = new Pessoal();

        pLista(
                $pessoal->get_nome($idServidor),
                $pessoal->get_cargoSimples($idServidor),
                $pessoal->get_lotacao($idServidor),
                date_to_php($dados["dtNom"])
        );
    }

    ###########################################################

    function exibeDescricaoComissao($idComissao) {
        /**
         * Exibe informações sobre a Nome do Laboratório, do Curso, da Gerência, da Diretoria ou da Pró Reitoria	
         * 
         * @note Usado na rotina de cadastro de Cargo em comissão de um detrerminado servidor
         * 
         * @syntax descricaoComissao($idComissao);
         * 
         * @param $idComissao integer null o id do cargo em comissão
         */
        # Conecta ao Banco de Dados
        $comissao = new CargoComissao();

        # Pega os dados da comissão
        $dados = $comissao->get_dados($idComissao);
        $descricao = $comissao->get_descricaoCargo($idComissao);
        $tipo = $dados['tipo'];

        echo $descricao;

        # Informa o tipo
        switch ($tipo) {

            case 1:
                br();
                label("Pro Tempore");
                break;

            case 2:
                br();
                label("Designado");
                break;
        }
        return;
    }

###########################################################

    function exibeOcupanteAnterior($idComissao) {

        /**
         * Exibe todos os dados de vagas de um cargo
         */
        # Pega a nomeação anterior
        $dados1 = $this->get_dados($idComissao);
        $idAnterior = $dados1['idAnterior'];
        $outraOrigem = $dados1['outraOrigem'];

        if (empty($idAnterior)) {
            if (empty($outraOrigem)) {
                return "---";
            } else {
                return $outraOrigem;
            }
        } else {

            # Pega o nome do nomeado anterio
            $dados2 = $this->get_dados($idAnterior);
            $idServidorAnterior = $dados2['idServidor'];

            $pessoal = new Pessoal();

            pLista(
                    $pessoal->get_nome($idServidorAnterior),
                    $pessoal->get_cargoSimples($idServidorAnterior),
                    $pessoal->get_lotacao($idServidorAnterior),
                    date_to_php($dados2['dtNom']) . ' - ' . date_to_php($dados2['dtExo'])
            );
        }
    }

    ###########################################################

    /**
     * Método get_nomeECargoEPerfil
     * fornece o nome, cargo e perfil de um servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    function get_nomeECargoSimplesEPerfil($idComissao) {
        if (empty($idComissao)) {
            return null;
        } else {
            $dados = $this->get_dados($idComissao);

            $pessoal = new Pessoal();
            $pessoal->get_nomeECargoSimplesEPerfil($dados["idServidor"]);

            # Verifica se tem observação, se tiver exibe uma figura com mouseover
            if (!empty($dados["obs"])) {

                $div = new Div("divEditaNota");
                $div->abre();
                toolTip("Obs", $dados["obs"]);
                $div->fecha();
            }
        }
    }

    ###########################################################

    function get_numServidoresDescricao($idDescricao) {

        /**
         * 
         * Informa o cargos cadastrados com esta descrição
         * 
         */
        # Pega os dados
        $select = "SELECT idComissao
                     FROM tbcomissao
                    WHERE idDescricaoComissao = {$idDescricao}";

        $pessoal = new Pessoal();
        return $pessoal->count($select);
    }

    ###########################################################

    /**
     * Método get_obs
     * 
     * Informa a observação
     */
    public function get_obs($idTipoCargo) {

        $select = 'SELECT obs                             
                     FROM tbtipocomissao 
                    WHERE idTipoComissao = ' . $idTipoCargo;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);
        return $row[0];
    }

    ###########################################################

    /**
     * Método exibeObs
     * 
     * Exibe a observação
     */
    public function exibeObs($idTipoCargo) {

        $select = 'SELECT obs                             
                     FROM tbtipocomissao 
                    WHERE idTipoComissao = ' . $idTipoCargo;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        if (is_null($row[0])) {
            echo "---";
        } else {
            toolTip("Obs", $row[0]);http://localhost/areaServidor/sistema/administracao.php
        }
    }

    ###########################################################

    /**
     * Método getReitorData
     * 
     * Informa o nome do reitor na data informada
     */
    public function get_idServidorReitorData($data) {

        $select = 'SELECT idServidor                             
                     FROM tbcomissao 
                    WHERE idTipoComissao = 13 
                      AND tipo <> 2 
                      AND (
                      (CAST("' . date_to_bd($data) . '" AS DATE) BETWEEN dtNom AND dtExo) OR (dtExo IS NULL AND dtNom <= CAST("' . date_to_bd($data) . '" AS DATE))
                      )';

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        return $row[0];
    }

    ###########################################################
}
