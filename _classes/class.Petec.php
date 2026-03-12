<?php

class Petec {
    ###########################################################

    function get_arrayPetec($idMarcador = null) {

        # Gera um array com os dados do Petec
        # Seguindo a seguinte ordem
        # 0 - Portaria
        # 1 - Data do Certificado
        # 2 - Horas Exigidas
        # 3 - Data limite de Entrega
        # 4 - Nome do arquivo pdf da portaria
        # 5 - Meses
        # 6 - Valor
        # Verifica se foi preenchido
        if (is_null($idMarcador)) {
            return null;
        } else {
            switch ($idMarcador) {
                case 4 :
                    return [
                        "418/25",
                        "21/07/2025",
                        20,
                        "10/03/2026",
                        74,
                        "Agosto, Setembro, Outubro e Novembro de 2025",
                        "R$ 3.000,00"
                    ];
                    break;

                case 5 :
                    return [
                        "473/25",
                        "15/12/2025",
                        10,
                        "10/03/2026",
                        75,
                        "Dezembro de 2025 e Janeiro de 2026",
                        "R$ 3.000,00"
                    ];
                    break;

                case 6 :
                    return [
                        "481/25",
                        "18/12/2025",
                        20,
                        "30/06/2026",
                        76,
                        "Fevereiro, Março, Abril e Maio de 2026",
                        "R$ 3.000,00"
                    ];
                    break;
            }
        }
    }

    ###########################################################

    function temPetec($idServidor, $idMarcador) {
        /**
         * Informa se o servidor tem certificados com esse marcador
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

    function estaInscrito($idServidor, $idMarcador) {
        /**
         * Informa se o servidor está inscrito no petec desse marcador
         */
        # Verifica se tem id
        if (empty($idServidor) OR empty($idMarcador)) {
            return false;
        } else {
            /**
             * Verifica se o servidor está inscrito no petec1
             */
            if ($idMarcador == 4 OR $idMarcador == 5) {

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
        }

        /**
         * Verifica se o servidor está inscrito no petec2
         */
        if ($idMarcador == 6) {

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
    }

###########################################################

    function exibeQuadroPortariasPetec($soPdf = false) {
        /**
         * Exibe um quadro com as regras das portarias
         */
        # Pega os ids dos marcadores Petec
        $formacao = new Formacao();
        $idMarcadoresPetec = $formacao->get_arrayMarcadores("Petec");

        # Monta o array
        foreach ($idMarcadoresPetec as $item) {

            # Pega os dados dessa portaria
            $dados = $this->get_arrayPetec($item[0]);

            # Monta o array
            if ($soPdf) {
                $array[] = [$dados[0], $dados[4]];
            } else {
                $array[] = [$dados[0], $item[0], $dados[4]];
            }
        }
        $tabela = new Tabela();

        if ($soPdf) {
            $tabela->set_titulo("PDFs das Portarias PETEC");
            $tabela->set_label(["Portaria", "Pdf"]);
            $tabela->set_width([50, 50]);
            $tabela->set_align(["center", "center"]);

            $tabela->set_classe([null, "petec"]);
            $tabela->set_metodo([null, "exibePdfPetec"]);
        } else {
            $tabela->set_titulo("Dados das Portarias PETEC");
            $tabela->set_label(["Portaria", "Dados", "Pdf"]);
            $tabela->set_width([20, 60, 20]);
            $tabela->set_align(["center", "center"]);

            $tabela->set_classe([null, "petec", "petec"]);
            $tabela->set_metodo([null, "exibeDadosPortaria", "exibePdfPetec"]);
        }

        $tabela->set_conteudo($array);
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

###########################################################

    function exibeQuadroPortariasPetec2() {
        /**
         * Exibe um quadro com as regras das portarias
         */
        # Pega os ids dos marcadores Petec
        $formacao = new Formacao();
        $idMarcadoresPetec = $formacao->get_arrayMarcadores("Petec");

        # Monta o array
        foreach ($idMarcadoresPetec as $item) {

            # 0 - Portaria
            # 1 - Data do Certificado
            # 2 - Horas Exigidas
            # 3 - Data limite de Entrega
            # 4 - Nome do arquivo pdf da portaria
            # 5 - Meses
            # 6 - Valor
            # Pega os dados dessa portaria
            $dados = $this->get_arrayPetec($item[0]);

            # Monta o array
            $array[] = [
                $dados[0], // Portaria
                $dados[2], // Horas
                $dados[3], // Entregar até
                $dados[1], // Curso iniciado em
                $dados[5], // Meses de pgto
                $dados[6], // Meses de pgto
                $dados[4], // Pdf
            ];
        }
        $tabela = new Tabela();
        $tabela->set_titulo("Dados das Portarias PETEC");
        $tabela->set_label(["Portaria", "Horas", "Entregar até", "Curso Iniciado após", "Pago em", "Valor", "pdf"]);
        #$tabela->set_width([20, 60, 20]);
        #$tabela->set_align(["center", "center"]);

        $tabela->set_classe([null, null, null, null, null, null, "petec"]);
        $tabela->set_metodo([null, null, null, null, null, null, "exibePdfPetec"]);

        $tabela->set_conteudo($array);
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

###########################################################

    function exibeQuadroInscritosPetec($lotacao = null) {
        /**
         * Exibe um quadro com 
         */
        # Conecta ao banco de dados
        $pessoal = new Pessoal();

        # Label da Lotação
        if (is_numeric($lotacao)) {
            $labelLotação = $pessoal->get_nomeLotacao2($lotacao);
        } else { # senão é uma diretoria genérica
            $labelLotação = $lotacao;
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
        $tabela->set_titulo("Inscrição de Servidores");
        $tabela->set_subtitulo($labelLotação);
        $tabela->set_label(["Servidores", "Portarias<br/>418/25 e 473/25", "Portaria<br/>481/25"]);
        $tabela->set_align(["left", "center", "center"]);
        $tabela->set_width([33, 33, 33]);
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

    function somatorioHorasPetec($idServidor = null, $idMarcador = null, $exibePortaria = false) {
        /**
         * Informa o somatorio de horas de um marcador
         */
        # Inicia as classes
        $pessoal = new Pessoal();

        # Verifica se tem id
        if (empty($idServidor) OR empty($idMarcador)) {
            return 0;
        } else {
            # Pega as variaveis
            $array = $this->get_arrayPetec($idMarcador);
            $horasExigidas = $array[2];
            $inscrito = $this->estaInscrito($idServidor, $idMarcador);

            # Pega as horas
            $formacao = new Formacao();
            $dados = $formacao->somatorioHoras($idServidor, $idMarcador);

            # Pega os valores
            $horasInformadas = $dados[0];
            $minutosInformados = $dados[1];

            # Formata para exibição
            if (empty($minutosInformados)) {
                $horasExibicao = "{$horasInformadas} h";
            } else {
                $horasExibicao = "{$horasInformadas} h e {$minutosInformados} m";
            }

            # Informa as horas
            p("Horas Informadas: {$horasExibicao}", "pHorasInformadas");
            p("Horas Exigidas: {$horasExigidas} h", "pHorasExigidas");

            # Calcula o que falta (se falta)
            $resultado = ($horasInformadas - $horasExigidas);

            # Verifica se está inscrito para esse Petec
            if (!$inscrito) {
                p("Servidor Não Inscrito", "pHorasFaltam");
            } else {
                # Se for inscrito exibe a situação
                if ($resultado >= 0) {
                    p("Situação OK", "pHoraOk");
                } else {
                    $resultado = abs($resultado);
                    if (empty($minutosInformados)) {
                        p("Faltam: {$resultado}h", "pHorasFaltam");
                    } else {
                        $resultado--;
                        $minutos = 60 - $minutosInformados;
                        p("Faltam: {$resultado}h e {$minutos} m", "pHorasFaltam");
                    }
                }
            }

            # Exibe os dados da Portaria
            if ($exibePortaria) {
                $this->exibeDadosPortaria($idMarcador);
            }
        }
    }

    ###########################################################

    function somatorioHoras4($idServidor) {
        /**
         * Informa o somatorio de horas do marcador 4
         * Petec - Portaria 418/25
         */
        $this->somatorioHorasPetec($idServidor, 4);
    }

    ###########################################################

    function somatorioHoras5($idServidor) {
        /**
         * Informa o somatorio de horas do marcador 5
         * Petec - Portaria 473/25
         */
        $this->somatorioHorasPetec($idServidor, 5);
    }

    ###########################################################

    function somatorioHoras6($idServidor) {
        /**
         * Informa o somatorio de horas do marcador 6
         * Petec - Portaria 481/25
         */
        $this->somatorioHorasPetec($idServidor, 6);
    }

    ###########################################################

    function somatorioHorasPortaria4($idServidor) {
        /**
         * Informa o somatorio de horas do marcador 4
         * Petec - Portaria 418/25
         */
        $this->somatorioHorasPetec($idServidor, 4, true);
    }

    ###########################################################

    function somatorioHorasPortaria5($idServidor) {
        /**
         * Informa o somatorio de horas de um marcador 
         * Petec - Portaria 473/25
         */
        $this->somatorioHorasPetec($idServidor, 5, true);
    }

    ###########################################################

    function somatorioHorasPortaria6($idServidor) {
        /**
         * Informa o somatorio de horas de um marcador 
         * Petec - Portaria 481/25
         */
        $this->somatorioHorasPetec($idServidor, 6, true);
    }

    ###########################################################

    function get_somatorioArredondadoHoras4($idServidor) {
        /**
         * Retorna o somatorio de horas do marcador 4
         * Petec - Portaria 418/25
         */
        $formacao = new Formacao();
        $dados = $formacao->somatorioHoras($idServidor, 4);

        # Retorna somente as horas
        return $dados[0];
    }

     ###########################################################

    function get_somatorioArredondadoMinutos4($idServidor) {
        /**
         * Retorna o somatorio de minutos do marcador 4
         * Petec - Portaria 418/25
         */
        $formacao = new Formacao();
        $dados = $formacao->somatorioHoras($idServidor, 4);
        
        # Retorna somente as horas
        return trataNulo($dados[1]);
    }

    ###########################################################

    function get_somatorioArredondadoHoras5($idServidor) {
        /**
         * Retorna o somatorio de horas do marcador 5
         * Petec - Portaria 473/25
         */
        $formacao = new Formacao();
        $dados = $formacao->somatorioHoras($idServidor, 5);

        # Retorna somente as horas
        return $dados[0];
    }

    ###########################################################

    function get_somatorioArredondadoMinutos5($idServidor) {
        /**
         * Retorna o somatorio de minutos do marcador 5
         * Petec - Portaria 418/25
         */
        $formacao = new Formacao();
        $dados = $formacao->somatorioHoras($idServidor, 5);
        
        # Retorna somente as horas
        return trataNulo($dados[1]);
    }

    ###########################################################

    function get_somatorioArredondadoHoras6($idServidor) {
        /**
         * Retorna o somatorio de horas do marcador 6
         * Petec - Portaria 481/25
         */
        $formacao = new Formacao();
        $dados = $formacao->somatorioHoras($idServidor, 6);

        # Retorna somente as horas
        return $dados[0];
    }

    ###########################################################

    function get_somatorioArredondadoMinutos6($idServidor) {
        /**
         * Retorna o somatorio de minutos do marcador 6
         * Petec - Portaria 418/25
         */
        $formacao = new Formacao();
        $dados = $formacao->somatorioHoras($idServidor, 6);
        
        # Retorna somente as horas
        return trataNulo($dados[1]);
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

    function exibeDadosPetec($idServidor) {

        # Limita a Tela 
        $grid = new Grid();
        $grid->abreColuna(3);

        /*
         * Exibe os pdf das portarias
         */

        $this->exibeQuadroPortariasPetec(true);

        $grid->fechaColuna();
        $grid->abreColuna(9);

        /*
         * Exibe os dados dos certificados entregues
         */

        tituloTable("Dados dos Certificados PETEC Entregues");

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

        $formacao = new Formacao();
        $petec = $formacao->get_arrayMarcadores("Petec");

        foreach ($petec as $item) {
            $label[] = $item[1];
            $align[] = "center";
            $classe[] = "Petec";
            $metodo[] = "somatorioHorasPortaria{$item[0]}"; // Gambiarra para fazer funcionar. Depois eu vejo um modo melhor de fazer isso...
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

        $grid->fechaColuna();
        $grid->fechaGrid();
    }

    ###########################################################

    function apagaTabelaCsv() {

        # Apaga a tabela 
        $select = 'SELECT idPetecImporta FROM tbpetecimporta';

        $pessoal = new Pessoal();
        $row = $pessoal->select($select);

        $pessoal->set_tabela("tbpetecimporta");
        $pessoal->set_idCampo("idPetecImporta");

        foreach ($row as $tt) {
            $pessoal->excluir($tt[0]);
        }
    }

    ###########################################################

    /*
     * Retorna o número de registros da tabela temporária do upload
     */

    function get_numRegistrosTabelaUpload() {


        $select = "SELECT idPetecImporta FROM tbpetecimporta";

        $pessoal = new Pessoal();
        return $pessoal->count($select);
    }

    ###########################################################

    /*
     * Retorna o número de registros da tabela temporária do upload
     */

    function get_numRegistrosTabelaUploadComErro() {


        $select = "SELECT idPetecImporta "
                . "  FROM tbpetecimporta "
                . " WHERE erro IS NOT NULL";

        $pessoal = new Pessoal();
        return $pessoal->count($select);
    }

    ###########################################################

    /*
     * Retorna o número de registros da tabela temporária do upload
     */

    function exibeDadosPortaria($idMarcador) {

        # Exibe os dados da Portaria
        $dados = $this->get_arrayPetec($idMarcador);

        p("Prazo de Entrega: {$dados[3]}", "pPetecLabel");
        #p($dados[3], "pPetecInfo");
        p("Mínimo de Horas: {$dados[2]}", "pPetecLabel");
        #p($dados[2], "pPetecInfo");
        p("Cursos a Partir de: {$dados[1]}", "pPetecLabel");
        #p($dados[1], "pPetecInfo");
    }

    ###########################################################

    /*
     * Retorna o número de registros da tabela temporária do upload
     */

    function exibeDadosPortaria2($idMarcador) {

        # Exibe os dados da Portaria
        $dados = $this->get_arrayPetec($idMarcador);

        #tituloTable("Portaria Petec {$dados[0]}");
        $painel = new Callout();
        $painel->abre();

        p("Prazo de Entrega:", "pPetecLabel2");
        p($dados[3], "pPetecInfo");
        p("Mínimo de Horas:", "pPetecLabel2");
        p($dados[2], "pPetecInfo");
        p("Cursos a Partir de:", "pPetecLabel2");
        p($dados[1], "pPetecInfo");
        p("Pgto para os meses de:", "pPetecLabel2");
        p($dados[5], "pPetecInfo");
        p("Valor:", "pPetecLabel2");
        p($dados[6], "pPetecInfo");

        $painel->fecha();
    }

    ###########################################################

    /*
     * Retorna o número de registros da tabela temporária do upload
     * 
     * @param $texto string null o idMarcador e o idservidor separados por ;
     *
     * @syntax $petec->exibeCertificadorMarcador($texto);
     */

    function exibeCursosMarcador($texto = null) {

        # Inicia as Classes
        $pessoal = new Pessoal();
        $formacao = new Formacao();

        # Verifica se tem texto
        if (empty($texto)) {
            return null;
        } else {
            $dados = $pieces = explode(";", $texto);

            # Verifica se tem idServidor
            if (empty($dados[0])) {
                return null;
            } else {
                $idServidor = $dados[0];
                $idPessoa = $pessoal->get_idPessoa($idServidor);
            }

            # Verifica se tem idMarcador
            if (empty($dados[1])) {
                return null;
            } else {
                $idMarcador = $dados[1];
            }

            # Monta o select
            $select = "SELECT habilitacao,
                              instEnsino,
                              horas,
                              minutos
                         FROM tbformacao 
                        WHERE idPessoa = {$idPessoa}
                          AND (tbformacao.marcador1 = {$idMarcador} OR
                               tbformacao.marcador2 = {$idMarcador} OR
                               tbformacao.marcador3 = {$idMarcador} OR
                               tbformacao.marcador4 = {$idMarcador})
                      ORDER BY anoTerm";

            $row = $pessoal->select($select);
            $count = $pessoal->count($select);
            $contador = 1;

            # Exibe os dados
            foreach ($row as $item) {

                # Trata as horas
                if (empty($item[3])) {
                    $horasExibicao = "{$item[2]} h";
                } else {
                    $horasExibicao = "{$item[2]} h {$item[3]} m";
                }

                plista(
                        $item[0],
                        $item[1],
                        $horasExibicao
                );

                # Verifica se tem hr
                if ($contador < $count) {
                    hr("grosso1");
                    $contador++;
                }
            }
        }
    }

    ###########################################################

    /*
     * Exibe informações do servidor e do Petec
     */

    function get_dadosServidorPetec4($idServidor = null) {

        # Verifica se tem id
        if (empty($idServidor)) {
            return null;
        } else {
            $pessoal = new Pessoal();
            $pessoal->get_nomeECargoSimplesELotacao($idServidor);

            # Exibe o Total de Horas
            $petec = new Petec();
            p("Total de Horas: " . $petec->get_somatorioArredondadoHoras4($idServidor));
        }
    }

    ###########################################################
}
