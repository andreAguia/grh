<?php

class Formacao {

    /**
     * Abriga as várias rotina do Cadastro de Formação Escolar do servidor
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ###########################################################

    function get_dados($id) {

        /**
         * Fornece os todos os dados de um id
         */
        # Pega os dados
        $select = "SELECT *
                     FROM tbformacao
                    WHERE idFormacao = {$id}";

        $pessoal = new Pessoal();
        return $pessoal->select($select, false);
    }

    ###########################################################

    function exibeCurso($id) {

        /**
         * Fornece Detalhes do curso
         */
        # Verifica se tem id
        if (empty($id)) {
            return null;
        } else {
            # Pega os dados
            $dados = $this->get_dados($id);

            # Trata carga horária
            if (!empty($dados['horas'])) {
                $dados['horas'] .= " horas";
            }

            pLista(
                    $dados['habilitacao'],
                    $dados['instEnsino'],
                    $dados['anoTerm'],
                    $dados['horas']
            );
        }
    }

    ###########################################################

    function get_escolaridade($idServidor) {
        /**
         * Fornece a escolaridade de um servidor seja pelo cargo, 
         * seja pelo cadastro de formação. o que tiver maior escolaridade
         */
        # inicia as variáveis
        $idEscolaridade = 0;

        # Conecta ao banco de dados
        $pessoal = new Pessoal();

        # Pega o idPessoa desse servidor
        $idPessoa = $pessoal->get_idPessoa($idServidor);

        # Pega o id cargo do servidor
        $idCargo = $pessoal->get_idCargo($idServidor);

        # Pega o cargo específico
        if (!empty($idCargo)) {
            $idTipoCargo = $pessoal->get_idTipoCargo($idCargo);

            # Pega a escolaridade do cargo
            switch ($idTipoCargo) {

                # Professorea
                case 1:
                case 2:
                    $idEscolaridade = 11;
                    break;

                # Profissional de Nível Superior
                case 3:
                    $idEscolaridade = 8;
                    break;

                # Profissional de Nível Médio
                case 4:
                    $idEscolaridade = 6;
                    break;

                # Profissional de Nível Fundamental
                case 5:
                    $idEscolaridade = 4;
                    break;

                # Profissional de Nível Elementar
                case 6:
                    $idEscolaridade = 2;
                    break;

                default:
                    $idEscolaridade = 0;
                    break;
            }
        }

        # Pega a escolaridade da tabela formação
        $select = "SELECT idEscolaridade
                     FROM tbformacao 
                    WHERE idEscolaridade <> 12 
                      AND idPessoa = {$idPessoa} 
                 ORDER BY idEscolaridade desc LIMIT 1";

        $dados = $pessoal->select($select, false);

        if ($dados) {
            # Pega a maior escolaridade
            $maior = maiorValor([$idEscolaridade, $dados['idEscolaridade']]);

            # Retorna a maior escolaridade registrada
            return $maior;
        } else {
            return $idEscolaridade;
        }
    }

    ###########################################################

    public function exibeBotaoUpload($idFormacao) {
        /**
         * Exibe um botão de upload
         * 
         * @param $idFormacao integer null O id 
         * 
         * @syntax $formacao->exibeBotaoUpload($idFormacao);
         */
        # Verifica se tem id
        if (empty($idFormacao)) {
            return null;
        } else {
            # Exibe o botão
            $link = new Link(null, "?fase=upload&id={$idFormacao}", "Upload o certificado / diploma do curso");
            $link->set_imagem(PASTA_FIGURAS . "upload.png", 20, 20);
            #$link->set_target("_blank");
            $link->show();
        }
    }

    ###########################################################

    public function exibeCertificado($idFormacao) {
        /**
         * Exibe um link para exibir o pdf do certificado
         * 
         * @param $idFormacao integer null O id
         * 
         * @syntax $formacao->exibeCertificado($idFormacao);
         */
        # Verifica se tem id
        if (empty($idFormacao)) {
            return null;
        } else {

            # Monta o arquivo
            $arquivo = PASTA_CERTIFICADO . $idFormacao . ".pdf";

            # Verifica se ele existe
            if (file_exists($arquivo)) {

                # Monta o link
                $link = new Link(null, $arquivo, "Exibe o certificado / diploma do curso");
                $link->set_imagem(PASTA_FIGURAS . 'doc.png', 20, 20);
                $link->set_target("_blank");
                $link->show();
            } else {
                label("Sem<br/>Comprovação", "alert");
            }
        }
    }

###########################################################

    function exibeMarcador($id) {

        /**
         * Fornece Detalhes do curso
         */
        # Verifica se tem id
        if (empty($id)) {
            return null;
        } else {
            # Pega os dados
            $dados = $this->get_dados($id);

            # Marcador 1
            if (!empty($dados['marcador1'])) {
                p($this->get_marcador($dados['marcador1']), "pNota");
            }

            # Marcador 2
            if (!empty($dados['marcador2'])) {
                p($this->get_marcador($dados['marcador2']), "pNota");
            }

            # Marcador 3
            if (!empty($dados['marcador3'])) {
                p($this->get_marcador($dados['marcador3']), "pNota");
            }

            # Marcador 4
            if (!empty($dados['marcador4'])) {
                p($this->get_marcador($dados['marcador4']), "pNota");
            }
        }
    }

    ###########################################################

    function get_arrayMarcadores($pesquisa = null) {
        /**
         * Fornece um array com os marcadores
         */
        $pessoal = new Pessoal();

        if (empty($pesquisa)) {
            $array = $pessoal->select("SELECT * FROM tbformacaomarcador");
        } else {
            $array = $pessoal->select("SELECT * FROM tbformacaomarcador WHERE marcador LIKE '%{$pesquisa}%'");
        }
        return $array;
    }

    ###########################################################

    function get_marcador($id = null) {
        /**
         * Informa o marcador de um idMarcador
         */
        $arrayMarcadores = $this->get_arrayMarcadores();

        foreach ($arrayMarcadores as $item) {
            if ($item[0] == $id) {
                return $item[1];
            }
        }
    }

    ###########################################################

    function temPetec($idServidor, $idMarcador) {
        /**
         * Informa se o servidor tem o marcador
         */
        # Verifica se tem id
        if (empty($idServidor) OR empty($idMarcador)) {
            return false;
        } else {
            # Passa o idservidor para idPessoa
            $pessoal = new Pessoal();
            $idPessoa = $pessoal->get_idPessoa($idServidor);

            # Select
            $select = "SELECT *
                         FROM tbformacao
                        WHERE (marcador1 = {$idMarcador} OR marcador2 = {$idMarcador} OR marcador3 = {$idMarcador} OR marcador4 = {$idMarcador})  
                          AND idPessoa = {$idPessoa}";

            $result = $pessoal->select($select);
            $quantidade = $pessoal->count($select);

            if ($quantidade == 0) {
                return false;
            } else {
                return true;
            }
        }
    }

    ###########################################################

    function exibeQuadroPetec() {
        /**
         * Exibe um quadro com as regras das portarias
         */
        # Monta o array
        $array = [
            ["Portaria 418/25", 20, "12/02/2026", 74],
            ["Portaria 473/25", 10, "10/03/2026", 75],
            ["Portaria 481/25", 20, "30/06/2026", 76],
        ];

        $tabela = new Tabela();
        $tabela->set_conteudo($array);
        $tabela->set_titulo("Portarias");
        $tabela->set_label(["Portaria", "Horas", "Prazo", "Pdf"]);
        $tabela->set_width([30, 20, 30, 20]);
        $tabela->set_align(["center", "center"]);

        $tabela->set_classe([null, null, null, "formacao"]);
        $tabela->set_metodo([null, null, null, "exibePdfPetec"]);

        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

    ###########################################################

    function exibeQuadroInscritos($lotacao = null) {
        /**
         * Exibe um quadro com as regras das portarias
         */
        # Conecta ao banco de dados
        $pessoal = new Pessoal();

        # Label da Lotação
        if (is_numeric($lotacao)) {
            $labelLotação = $pessoal->get_nomeLotacao2($lotacao);
        } else { # senão é uma diretoria genérica
            $labelLotação = $parametroLotacao;
        }

        # Monta o array
        # Petec1 - Inscritos
        $select1 = 'SELECT count(idServidor)
                      FROM tbservidor JOIN tbperfil USING (idPerfil)
                                      JOIN tbhistlot USING (idServidor)
                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       AND situacao = 1
                       AND petec1 = "s"
                       AND tbperfil.tipo <> "Outros"';

        # Verifica se tem filtro por lotação
        if ($lotacao <> "Todos") {  // senão verifica o da classe
            if (is_numeric($lotacao)) {
                $select1 .= " AND (tblotacao.idlotacao = {$lotacao})";
            } else { # senão é uma diretoria genérica
                $select1 .= " AND (tblotacao.DIR = '{$lotacao}')";
            }
        }

        $row1 = $pessoal->select($select1, false);

        # Petec1 - Não Inscritos
        $select2 = 'SELECT count(idServidor)
                      FROM tbservidor JOIN tbperfil USING (idPerfil)
                                      JOIN tbhistlot USING (idServidor)
                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       AND situacao = 1
                           AND (petec1 is null OR petec1 != "s")
                           AND tbperfil.tipo <> "Outros"';

        # Verifica se tem filtro por lotação
        if ($lotacao <> "Todos") {  // senão verifica o da classe
            if (is_numeric($lotacao)) {
                $select2 .= " AND (tblotacao.idlotacao = {$lotacao})";
            } else { # senão é uma diretoria genérica
                $select2 .= " AND (tblotacao.DIR = '{$lotacao}')";
            }
        }

        $row2 = $pessoal->select($select2, false);

        # Petec2 - Inscritos
        $select3 = 'SELECT count(idServidor)
                      FROM tbservidor JOIN tbperfil USING (idPerfil)
                                      JOIN tbhistlot USING (idServidor)
                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       AND situacao = 1
                           AND petec2 = "s"
                           AND tbperfil.tipo <> "Outros"';

        # Verifica se tem filtro por lotação
        if ($lotacao <> "Todos") {  // senão verifica o da classe
            if (is_numeric($lotacao)) {
                $select3 .= " AND (tblotacao.idlotacao = {$lotacao})";
            } else { # senão é uma diretoria genérica
                $select3 .= " AND (tblotacao.DIR = '{$lotacao}')";
            }
        }

        $row3 = $pessoal->select($select3, false);

        # Petec2 - Não Inscritos
        $select4 = 'SELECT count(idServidor)
                      FROM tbservidor JOIN tbperfil USING (idPerfil)
                                      JOIN tbhistlot USING (idServidor)
                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       AND situacao = 1
                           AND (petec2 is null OR petec2 != "s")
                           AND tbperfil.tipo <> "Outros"';

        # Verifica se tem filtro por lotação
        if ($lotacao <> "Todos") {  // senão verifica o da classe
            if (is_numeric($lotacao)) {
                $select4 .= " AND (tblotacao.idlotacao = {$lotacao})";
            } else { # senão é uma diretoria genérica
                $select4 .= " AND (tblotacao.DIR = '{$lotacao}')";
            }
        }
        $row4 = $pessoal->select($select4, false);

        # Tabela
        $tabela = new Tabela();
        $tabela->set_conteudo([
            ["Inscritos", $row1[0], $row3[0]],
            ["NÃO Inscritos", $row2[0], $row4[0]],
        ]);
        $tabela->set_titulo("Servidores Ativos");
        $tabela->set_subtitulo($labelLotação);
        $tabela->set_label(["Servidores", "Quantidade", "Quantidade"]);
        $tabela->set_align(["left", "center", "center"]);
        $tabela->set_totalRegistro(false);
        $tabela->set_colunaSomatorio([1, 2]);
        $tabela->show();
    }

    ##############################################################

    public function exibePdfPetec($id = null) {

        # Verifica se o id foi informado
        if (empty($id)) {
            return "---";
        } else {

            # Monta o arquivo
            $arquivo = PASTA_DOCUMENTOS . "{$id}.pdf";

            # Verifica se ele existe
            if (file_exists($arquivo)) {

                $botao = new BotaoGrafico();
                $botao->set_url($arquivo);
                $botao->set_imagem(PASTA_FIGURAS . 'doc.png', 20, 20);
                $botao->set_title("Exibe o Pdf");
                $botao->set_target("_blank");
                $botao->show();
            } else {
                return "---";
            }
        }
    }

###########################################################

    function somatorioHoras4($idServidor) {
        /**
         * Informa o somatorio de horas de um marcador
         * Recebe na forma de array para ser usada na classe de tabelas
         */
        # Separa as variaveis
        $idMarcador = 4;
        $horas = 20;

        # Verifica se tem id
        if (empty($idServidor) OR empty($idMarcador)) {
            return 0;
        } else {
            # Passa o idservidor para idPessoa
            $pessoal = new Pessoal();
            $idPessoa = $pessoal->get_idPessoa($idServidor);

            # Select
            $select = "SELECT SUM(horas)
                         FROM tbformacao
                        WHERE (marcador1 = {$idMarcador} OR marcador2 = {$idMarcador} OR marcador3 = {$idMarcador} OR marcador4 = {$idMarcador})  
                          AND idPessoa = {$idPessoa}";

            $result = $pessoal->select($select, false);
            if (empty($result[0])) {
                $hInfo = 0;
            } else {
                $hInfo = $result[0];
            }

            p("Horas Informadas: {$hInfo}h", "pHorasInformadas");
            p("Horas Exigidas: {$horas}h", "pHorasExigidas");
            hr("geral1");

            $resultado = ($result[0] - $horas);

            if ($resultado >= 0) {
                p("Total: {$resultado}h", "pHoraOk");
            } else {
                $resultado = abs($resultado);
                p("Faltam: {$resultado}h", "pHorasFaltam");
            }

            # Verifica se está inscrito para esse Petec
            if (!$this->petec1($idServidor)) {
                label("Servidor Não Inscrito", "warning");
            }
        }
    }

    ###########################################################

    function somatorioHoras5($idServidor) {
        /**
         * Informa o somatorio de horas de um marcador 
         * Petec - Portaria 473/25
         */
        # Separa as variaveis
        $idMarcador = 5;
        $horas = 10;

        # Verifica se tem id
        if (empty($idServidor) OR empty($idMarcador)) {
            return 0;
        } else {
            # Passa o idservidor para idPessoa
            $pessoal = new Pessoal();
            $idPessoa = $pessoal->get_idPessoa($idServidor);

            # Select
            $select = "SELECT SUM(horas)
                         FROM tbformacao
                        WHERE (marcador1 = {$idMarcador} OR marcador2 = {$idMarcador} OR marcador3 = {$idMarcador} OR marcador4 = {$idMarcador})  
                          AND idPessoa = {$idPessoa}";

            $result = $pessoal->select($select, false);
            if (empty($result[0])) {
                $hInfo = 0;
            } else {
                $hInfo = $result[0];
            }

            p("Horas Informadas: {$hInfo}h", "pHorasInformadas");
            p("Horas Exigidas: {$horas}h", "pHorasExigidas");
            hr("geral1");

            $resultado = ($result[0] - $horas);

            if ($resultado >= 0) {
                p("Total: {$resultado}h", "pHoraOk");
            } else {
                $resultado = abs($resultado);
                p("Faltam: {$resultado}h", "pHorasFaltam");
            }

            # Verifica se está inscrito para esse Petec
            if (!$this->petec1($idServidor)) {
                label("Servidor Não Inscrito", "warning");
            }
        }
    }

    ###########################################################

    function somatorioHoras6($idServidor) {
        /**
         * Informa o somatorio de horas de um marcador 
         * Petec - Portaria 481/25
         */
        # Separa as variaveis
        $idMarcador = 6;
        $horas = 20;

        # Verifica se tem id
        if (empty($idServidor) OR empty($idMarcador)) {
            return 0;
        } else {
            # Passa o idservidor para idPessoa
            $pessoal = new Pessoal();
            $idPessoa = $pessoal->get_idPessoa($idServidor);

            # Select
            $select = "SELECT SUM(horas)
                         FROM tbformacao
                        WHERE (marcador1 = {$idMarcador} OR marcador2 = {$idMarcador} OR marcador3 = {$idMarcador} OR marcador4 = {$idMarcador})  
                          AND idPessoa = {$idPessoa}";

            $result = $pessoal->select($select, false);
            if (empty($result[0])) {
                $hInfo = 0;
            } else {
                $hInfo = $result[0];
            }

            p("Horas Informadas: {$hInfo}h", "pHorasInformadas");
            p("Horas Exigidas: {$horas}h", "pHorasExigidas");
            hr("geral1");

            $resultado = ($result[0] - $horas);

            if ($resultado >= 0) {
                p("Total: {$resultado}h", "pHoraOk");
            } else {
                $resultado = abs($resultado);
                p("Faltam: {$resultado}h", "pHorasFaltam");
            }

            # Verifica se está inscrito para esse Petec
            if (!$this->petec1($idServidor)) {
                label("Servidor Não Inscrito", "warning");
            }
        }
    }

    ###########################################################

    function exibeDadosPetec($idServidor) {
        /**
         * Exibe uma tela com os dados do Petec
         */
        tituloTable("Dados Petec");
        $painel2 = new Callout();
        $painel2->abre();

        # Limita a tela
        $grid1 = new Grid();
        $grid1->abreColuna(5);

        # Exibe a tabela da regras
        $this->exibeQuadroPetec();

        $grid1->fechaColuna();
        $grid1->abreColuna(7);

        # Exibe a tabela do servidor
        $select = "SELECT tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                     FROM tbservidor 
                    WHERE idServidor = {$idServidor}";

        $pessoal = new Pessoal();
        $result2 = $pessoal->select($select);

        # Define as colunas
        $label = array();
        $align = array();
        $classe = array();
        $metodo = array();

        $petec = $this->get_arrayMarcadores("Petec");

        foreach ($petec as $item) {
            $label[] = $item[1];
            $align[] = "center";
            $classe[] = "Formacao";
            $metodo[] = "somatorioHoras{$item[0]}"; // Gambiarra para fazer funcionar. Depois eu vejo um modo melhor de fazer isso...
        }

        $tabela = new Tabela();
        #$tabela->set_titulo("Análise Geral");
        $tabela->set_conteudo($result2);

        $tabela->set_label($label);
        $tabela->set_align($align);

        $tabela->set_classe($classe);
        $tabela->set_metodo($metodo);
        $tabela->set_totalRegistro(false);
        $tabela->show();

        # Editar
        $menu2 = new MenuBar();
        $botao1 = new Link("Editar Inscrição Petec", "servidorPetec.php");
        $botao1->set_class('button');
        $menu2->add_link($botao1, "right");
        $menu2->show();

        $grid1->fechaColuna();
        $grid1->fechaGrid();

        $painel2->fecha();
    }

    ###########################################################

    function petec1($idServidor) {
        /**
         * Verifica se o servidor está inscrito no petec1
         */
        $select = "SELECT petec1
                     FROM tbservidor
                    WHERE idServidor = {$idServidor}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        if ($row[0] == "s") {
            return true;
        } else {
            return false;
        }
    }

    ###########################################################

    function exibeIncricaoPetec1($idServidor) {
        /**
         * Verifica se o servidor está inscrito no petec1
         */
        if ($this->petec1($idServidor)) {
            p("Inscrito", "pHoraOk");
        } else {
            p("Não Inscrito", "pHorasFaltam");
        }
    }

    ###########################################################

    function petec2($idServidor) {
        /**
         * Verifica se o servidor está inscrito no petec2
         */
        $select = "SELECT petec2
                     FROM tbservidor
                    WHERE idServidor = {$idServidor}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        if ($row[0] == "s") {
            return true;
        } else {
            return false;
        }
    }

    ###########################################################

    function exibeIncricaoPetec2($idServidor) {
        /**
         * Verifica se o servidor está inscrito no petec2
         */
        if ($this->petec2($idServidor)) {
            p("Inscrito", "pHoraOk");
        } else {
            p("Não Inscrito", "pHorasFaltam");
        }
    }

    ###########################################################
}
