<?php

class Publicacao {

    /**
     * Abriga as várias rotina referentes as publicações
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     * 
     * @var private $idConcursoPublicacao integer null O id da publicação
     */
##############################################################

    public function get_dados($id = null) {

        /**
         * Informa os dados da base de dados
         * 
         * @param $id integer null O id 
         * 
         * @syntax $publicacao->get_dados([$id]);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (empty($id)) {
            alert("É necessário informar o id da Publicação.");
            return;
        }

        # Pega os dados
        $select = "SELECT * 
                     FROM tbpublicacao
                    WHERE idPublicacao = {$id}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row;
    }

###########################################################

    public function exibePdf($id) {
        /**
         * Exibe um link para o pdf
         * 
         * @param $id integer null O id
         * 
         * @syntax $publicacao->exibePdf($id);
         */
        # Monta o arquivo
        $arquivo = PASTA_PUBLICACAO . $id . ".pdf";

        # Verifica se ele existe
        if (file_exists($arquivo)) {

            # Monta o link
            $link = new Link(null, $arquivo, "Exibe a Publicação");
            $link->set_imagem(PASTA_FIGURAS . "olho.png", 20, 20);
            $link->set_target("_blank");
            $link->show();
        } else {
            $link = new Link(null, "?fase=uploadPublicacao&id=$id", "Upload da Publicação");
            $link->set_imagem(PASTA_FIGURAS . "upload.png", 20, 20);
            #$link->set_target("_blank");
            $link->show();
        }
    }

###########################################################

    public function exibePublicacao($id) {
        /**
         * Exibe informações da publicação 
         * 
         * @param $id integer null O id
         * 
         * @syntax $publicacao->exibePublicacao($id);
         */
        # Verifica se o id foi informado
        if (empty($id)) {
            echo "---";
        } else {
            # Pega os dados
            $select = "SELECT data,
                              descricao,
                              tbtipopublicacao.nome,
                              pag,
                              idPublicacao
                     FROM tbpublicacao JOIN tbtipopublicacao USING (idTipoPublicacao)
                    WHERE idPublicacao = {$id}";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select);

            # Exibe a tabels
            $tabela = new Tabela();
            $tabela->set_titulo("Dados da Publicação");
            $tabela->set_conteudo($row);
            $tabela->set_label(["Data", "Descrição", "Tipo", "Página"]);
            $tabela->set_width([10, 40, 30, 10]);
            $tabela->set_funcao(["date_to_php"]);

            #$tabela->set_align(["left", "left"]);
            $tabela->set_totalRegistro(false);
            #$tabela->set_mensagemPosTabela($dados["idPublicacao"]);
            $tabela->set_idCampo('idPublicacao');
            $tabela->set_editar('?fase=editar');

            # pinta de cinza a tabela
            $formatacaoCondicional = array(
                array('coluna' => 3,
                    'valor' => $row[0][3],
                    'operador' => '=',
                    'id' => 'listaDados'));
            $tabela->set_formatacaoCondicional($formatacaoCondicional);

            $tabela->show();
        }
    }

###########################################################

    public function exibeServidoresImpactados($id) {
        /**
         * Exibe os serviudores impactados pela publicação
         * 
         * @param $id integer null O id
         * 
         * @syntax $publicacao->exibeServidoresImpactados($id);
         */
        # Conecta ao Banco de Dados        
        $pessoal = new Pessoal();

        # Verifica se o id foi informado
        if (empty($id)) {
            echo "---";
        } else {
            # Pega os dados
            $dados = $this->get_dados($id);

            # Monta o array
            $select = "SELECT idFuncional, 
                              nome,
                              idservidor,
                              idservidor,
                              idservidor,
                              idservidor,
                              idPublicacaoServidor
                         FROM tbpublicacaoservidor JOIN tbservidor USING (idServidor)
                              JOIN tbpessoa USING (idPessoa)
                        WHERE idPublicacao = {$id}
                     ORDER BY situacao, tbpessoa.nome";

            $result = $pessoal->select($select);

            # Exibe a tabels
            $tabela = new Tabela();
            $tabela->set_titulo("Servidores Impactados pela Publicação");
            $tabela->set_conteudo($result);
            $tabela->set_label(["Idfuncional", "Nome", "Cargo", "Lotação", "Perfil", "Situação", "Retirar"]);
            #$tabela->set_width([60, 15, 15, 10]);
            $tabela->set_align(["center", "left", "left", "left"]);

            $tabela->set_classe([null, null, "Pessoal", "Pessoal", "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, "get_cargo", "get_lotacao", "get_perfilSimples", "get_situacao"]);

            # Botão de exclusao
            $botao1 = new BotaoGrafico();
            $botao1->set_label('');
            $botao1->set_title('Retira o servidor desta publicação');
            $botao1->set_confirma('Deseja mesmo retirar este servidor desta publicação?');
            $botao1->set_url("?fase=excluirServidor&id=");
            $botao1->set_imagem(PASTA_FIGURAS_GERAIS . 'bullet_cross.png', 20, 20);

            # Coloca o objeto link na tabela			
            $tabela->set_link([null, null, null, null, null, null, $botao1]);
            $tabela->show();
        }
    }

###########################################################

    public function exibeServidoresImpactadosTabela($id) {
        /**
         * Exibe os serviudores impactados pela publicação
         * 
         * @param $id integer null O id
         * 
         * @syntax $publicacao->exibeServidoresImpactados($id);
         */
        # Conecta ao Banco de Dados        
        $pessoal = new Pessoal();

        # Verifica se o id foi informado
        if (empty($id)) {
            echo "---";
        } else {
            # Pega os dados
            $dados = $this->get_dados($id);

            # Monta o array
            $select = "SELECT tbpessoa.nome
                         FROM tbpublicacaoservidor JOIN tbservidor USING (idServidor)
                              JOIN tbpessoa USING (idPessoa)
                        WHERE idPublicacao = {$id}
                     ORDER BY situacao, tbpessoa.nome";

            $result = $pessoal->select($select);

            if (empty($result[0])) {
                return null;
            } else {
                # Percorre o array
                foreach ($result as $item) {
                    echo $item[0];
                    br();
                }
            }
        }
    }

###########################################################
}
