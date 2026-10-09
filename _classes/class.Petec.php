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
        # 7 - Tema do Curso
        # 8 - Campo no tbservidor
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
                        "R$ 3.000,00",
                        "Curso cuja temática envolva, de algum modo, o aperfeiçoamento tecnológico, inclusive, mas não somente, na área de inteligência artificial.",
                        "petec1"
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
                        "R$ 3.000,00",
                        "Curso cuja temática envolva, de algum modo, o aperfeiçoamento tecnológico, inclusive, mas não somente, na área de inteligência artificial.",
                        "petec1"
                    ];
                    break;

                case 6 :
                    return [
                        "481/25",
                        "18/12/2025",
                        20,
                        "31/07/2026",
                        76,
                        "Fevereiro, Março, Abril e Maio de 2026",
                        "R$ 3.000,00",
                        "Curso(s) cuja temática envolva o aprimoramento tecnológico, inclusive, mas não exclusivamente, na área de inteligência artificial.",
                        "petec2"
                    ];
                    break;

                case 8 :
                    return [
                        "518/26",
                        "29/04/2026",
                        20,
                        "18/12/2026",
                        88,
                        "Junho, Julho, Agosto, Setembro, Outubro, Novembro e Dezembro de 2026",
                        "R$ 3.000,00",
                        "Curso devendo obrigatoriamente contemplar as duas temáticas: Inteligência Artificial (IA) e Lei Geral de Proteção de Dados (LGPD).",
                        "petec3"
                    ];
                    break;

                case 9 :
                    return [
                        "557/26",
                        "29/09/2026",
                        30,
                        "30/06/2027",
                        95,
                        "Janeiro, Fevereiro, Março, Abril, Maio e Junho de 2027",
                        "R$ 3.000,00",
                        "I - Desenvolvimento, inclusão e acolhimento de estudantes e membros da comunidade universitária com necessidades específicas;<br/>"
                        . "II - Governança digital, gestão de processos e segurança da informação;<br/>"
                        . "III - Aplicação prática e observância da Lei Geral de Proteção de Dados Pessoais - LGPD.",
                        "petec4"
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

            # Pega os dados desse marcados
            $array = $this->get_arrayPetec($idMarcador);

            # Pega o campo na tbservidor
            $campo = $array[8];

            # Verifica se o servidor está inscrito

            $select = "SELECT {$campo}
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

    function exibeNumInscritos($idMarcador = null, $idLotacao = "Todos") {
        /**
         * Exibe um Callout co o número de inscritos
         */
        # Verifica se tem id
        if (empty($idMarcador)) {
            return false;
        } else {
            calloutWarning($this->get_numInscritos($idMarcador, $idLotacao) . " Servidores", "Inscritos", "center");
        }
    }

    ###########################################################

    /*
     * Retorna o número de servidores inscritos em um petec e uma lotação
     */

    function get_numInscritos($idMarcador = null, $lotacao = "Todos") {

        # Verifica se foi informada a petec
        if (empty($idMarcador)) {
            return null;
        } else {

            # Pega os dados desse marcados
            $array = $this->get_arrayPetec($idMarcador);

            # Pega o campo na tbservidor
            $campo = $array[8];

            # Monta o select
            $select1 = "SELECT count(idServidor)
                      FROM tbservidor JOIN tbperfil USING (idPerfil)
                                      JOIN tbhistlot USING (idServidor)
                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       AND situacao = 1
                       AND {$campo} = 's'
                       AND tbperfil.tipo <> 'Outros'";

            # Verifica se tem filtro por lotação
            if ($lotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($lotacao)) {
                    $select1 .= " AND (tblotacao.idlotacao = {$lotacao})";
                } else { # senão é uma diretoria genérica
                    $select1 .= " AND (tblotacao.DIR = '{$lotacao}')";
                }
            }

            $pessoal = new Pessoal();
            $row = $pessoal->select($select1, false);

            return $row[0];
        }
    }

    ###########################################################

    /*
     * Retorna o número de servidores não inscritos em um petec e uma lotação
     */

    function get_numNaoInscritos($idMarcador = null, $lotacao = null) {

        # Verifica se foi informada a petec
        if (empty($idMarcador)) {
            return null;
        } else {
            # Pega os dados desse marcados
            $array = $this->get_arrayPetec($idMarcador);

            # Pega o campo na tbservidor
            $campo = $array[8];

            # Monta o select
            $select1 = "SELECT count(idServidor)
                      FROM tbservidor JOIN tbperfil USING (idPerfil)
                                      JOIN tbhistlot USING (idServidor)
                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       AND situacao = 1
                       AND ({$campo} is null OR {$campo} != 's')
                       AND tbperfil.tipo <> 'Outros'";

            # Verifica se tem filtro por lotação
            if ($lotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($lotacao)) {
                    $select1 .= " AND (tblotacao.idlotacao = {$lotacao})";
                } else { # senão é uma diretoria genérica
                    $select1 .= " AND (tblotacao.DIR = '{$lotacao}')";
                }
            }

            $pessoal = new Pessoal();
            $row = $pessoal->select($select1, false);

            return $row[0];
        }
    }

    ###########################################################

    function exibeQuadroInscritosPetec($lotacao = null) {
        /**
         * Exibe um quadro com 
         */
        # Conecta ao banco de dados
        $pessoal = new Pessoal();

        # Pega os Marcadores Petec
        $formacao = new Formacao();
        $marcadoresPetec = $formacao->get_arrayMarcadores("Petec");

        # Inicia o array da tabela
        $arrayTabela = array();

        # Percorre o array de marcadores e preenche a tabela
        foreach ($marcadoresPetec as $item) {

            # Pega os dados do marcador
            $dados = $this->get_arrayPetec($item["idFormacaoMarcador"]);

            # Monta o array
            $arrayTabela[] = [$dados[0], $this->get_numInscritos($item["idFormacaoMarcador"], $lotacao), $this->get_numNaoInscritos($item["idFormacaoMarcador"], $lotacao)];
        }

        # Label da Lotação
        if (is_numeric($lotacao)) {
            $labelLotação = $pessoal->get_nomeLotacao2($lotacao);
        } else { # senão é uma diretoria genérica
            $labelLotação = $lotacao;
        }


        # Tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($arrayTabela);
        $tabela->set_titulo("Inscrição de Servidores");
        $tabela->set_subtitulo($labelLotação);
        $tabela->set_label(["Portarias", "Inscritos", null]);
        $tabela->set_colspanLabel([null, 2]);
        $tabela->set_label2([null, "Sim", "Não"]);
        $tabela->set_align(["center"]);
        $tabela->set_width([40, 30, 30]);
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

    ##############################################################

    function exibeQuadroPortariasPetec() {
        /**
         * Exibe um quadro com as regras das portarias
         * Utilizado na rotina da Área do Petec quando se escolhe a opção Geral
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
        $tabela->set_width([8, 8, 12, 12, 30, 12, 5]);

        $tabela->set_classe([null, null, null, null, null, null, "petec"]);
        $tabela->set_metodo([null, null, null, null, null, null, "exibePdfPetec"]);

        $tabela->set_rowspan(0);
        $tabela->set_grupoCorColuna(0);

        $tabela->set_conteudo($array);
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

    ###########################################################

    public function exibePdfPetec($id = null) {

        # Verifica se o id foi informado
        if (!empty($id)) {

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

//                p("PDF da Portaria", "pPetecLabel");
//                hr("geral2");
            }
        }
    }

    ###########################################################

    /*
     *  Exibe o somatório de um petec
     */

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

            # Exibe a Portaria
            IF ($exibePortaria) {
                $this->exibePdfPetec($array[4]);
            }

            # Pega as horas
            $formacao = new Formacao();
            $dados = $formacao->somatorioHoras($idServidor, $idMarcador);

            # Pega os temas para Petec 518/26
            if ($idMarcador == 8 OR $idMarcador == 9) {
                $temas = $this->get_temas($idServidor, $idMarcador);
            }

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
                    if ($idMarcador == 8) { // Verifica se é Petec 518/26
                        // Verifica se o array tem IA
                        if (in_array("IA", array_column($temas, 'tema')) AND in_array("LGPD", array_column($temas, 'tema'))) {
                            p("Situação OK", "pHoraOk");
                        } else {
                            p("Não Tem os Dois Temas Exigidos na Portaria", "pHorasFaltam");
                        }
                    } elseif ($idMarcador == 9) { // Verifica se é Petec 557/26
                        // Verifica se o array tem IA
                        if (in_array("Acolhimento", array_column($temas, 'tema')) AND in_array("LGPD", array_column($temas, 'tema')) AND in_array("Governança", array_column($temas, 'tema'))) {
                            p("Situação OK", "pHoraOk");
                        } else {
                            p("Não Tem os Temas Exigidos na Portaria", "pHorasFaltam");
                        }
                    } else {
                        p("Situação OK", "pHoraOk");
                    }
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

    function exibeDadosPetec($idServidor) {

        # Limita a Tela 
        $grid = new Grid();
        $grid->abreColuna(12);

        $pessoal = new Pessoal();

        /*
         * Exibe os dados dos certificados entregues
         */

        tituloTable("Dados dos Certificados PETEC Entregues");

        # Exibe a tabela do servidor
        $select = "SELECT tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                     FROM tbservidor 
                    WHERE idServidor = {$idServidor}";

        $result2 = $pessoal->select($select);

        # Define as colunas
        $label = array();
        $align = array();
        $valign = array();
        $classe = array();
        $metodo = array();
        $width = array();

        $formacao = new Formacao();
        $petec = $formacao->get_arrayMarcadores("Petec");

        foreach ($petec as $item) {
            $label[] = $item[1];
            $align[] = "center";
            $valign[] = "top";
            $classe[] = "Petec";
            $metodo[] = "somatorioHorasPortaria{$item[0]}"; // Gambiarra para fazer funcionar. Depois eu vejo um modo melhor de fazer isso...
            $width[] = 100 / count($petec);
        }

        $tabela = new Tabela();
        #$tabela->set_titulo("Análise Geral");
        $tabela->set_conteudo($result2);

        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_valign($valign);
        $tabela->set_width($width);

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
     * Exibe Dados da Portaria
     */

    function exibeDadosPortaria($idMarcador) {

        # Exibe os dados da Portaria
        $dados = $this->get_arrayPetec($idMarcador);

        # Tema
        p("Tema do Curso:", "pPetecLabel");
        p($dados[7], "pPetecTema");

        # Horas
        p("Mínimo de Horas: {$dados[2]}", "pPetecLabel");

        # A Partir de
        p("Cursos a Partir de: {$dados[1]}", "pPetecLabel");

        # Prazo de Entrega
        p("Prazo de Entrega: {$dados[3]}", "pPetecLabel");
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
     * Retorna array com os temas
     */

    function get_temas($idServidor, $idMarcador) {

        # Verifica se foi informada a petec
        if (empty($idMarcador) OR empty($idServidor)) {
            return null;
        } else {
            # Petec - Inscritos
            $select1 = "SELECT tema
                          FROM tbformacao LEFT JOIN tbservidor USING (idPessoa)
                         WHERE idServidor = {$idServidor}
                           AND (tbformacao.marcador1 = {$idMarcador} OR
                               tbformacao.marcador2 = {$idMarcador} OR
                               tbformacao.marcador3 = {$idMarcador} OR
                               tbformacao.marcador4 = {$idMarcador})";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select1);

            return $row;
        }
    }

    ###########################################################

    /*
     * Exibe Quadro Petec
     */

    function exibeQuadroPetec($idServidor, $relatorio = false) {

        # Conecta
        $pessoal = new Pessoal();

        $formacao = new Formacao();
        $petec = $formacao->get_arrayMarcadores("Petec");

        # Exibe a tabela de valores
        if ($relatorio) {
            echo '<table class="tabelaRelatorio" border="0">';
            p("Dados dos Certificados PETEC Entregues", "pRelatorioSubtitulo");
        } else {
            echo "<table class='tabelaPadrao'>";
            echo '<caption>Dados dos Certificados PETEC Entregues</caption>';
        }

        # Cabeçalho
        echo "<tr>";
        foreach ($petec as $item) {
            echo "<th>";
            echo $item[1];
            echo "</th>";
        }
        echo "</tr>";
        echo "<tr>";

        # Percorre o array
        foreach ($petec as $item) {
            # Pega os dados desse marcados
            $array = $this->get_arrayPetec($item[0]);

            # Pega as variaveis
            $array = $this->get_arrayPetec($item[0]);
            $horasExigidas = $array[2];
            $inscrito = $this->estaInscrito($idServidor, $item[0]);

            # Monta a célula da tabela
            echo "<td style='vertical-align: top;'>";

            # Pdf da Portaria
            if (!$relatorio) {
                $this->exibePdfPetec($array[4]);
            }

            # Pega as horas
            $formacao = new Formacao();
            $dados = $formacao->somatorioHoras($idServidor, $item[0]);

            # Pega os temas para Petec 518/26
            if ($item[0] == 8) {
                $temas = $this->get_temas($idServidor, $item[0]);
            }

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
                    // Verifica se é Petec 518/26
                    // Que tem regra do tema do curso
                    if ($item[0] == 8) {
                        // Verifica se o array tem IA
                        if (in_array("IA", array_column($temas, 'tema')) AND in_array("LGPD", array_column($temas, 'tema'))) {
                            p("Situação OK", "pHoraOk");
                        } else {
                            p("Não Tem os Dois Temas Exigidos na Portaria", "pHorasFaltam");
                        }
                    } else {
                        p("Situação OK", "pHoraOk");
                    }
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
            $this->exibeDadosPortaria($item[0]);
            echo "</td>";
        }

        echo "</tr>";
        echo "</table>";
    }

    ###########################################################
    # As rotinas abaixo podem ser melhoradas 
    ###########################################################

    function exibeIncricaoPetec1($idServidor) {
        /**
         * Verifica se o servidor está inscrito no respectivo petec
         */
        # Verifica se está inscrito
        if ($this->estaInscrito($idServidor, 4)) {
            p("Inscrito", "pHoraOk");
        } else {
            p("Não Inscrito", "pHorasFaltam");
        }
    }

    ###########################################################

    function exibeIncricaoPetec2($idServidor) {
        /**
         * Verifica se o servidor está inscrito no respectivo petec
         */
        # Verifica se está inscrito
        if ($this->estaInscrito($idServidor, 6)) {
            p("Inscrito", "pHoraOk");
        } else {
            p("Não Inscrito", "pHorasFaltam");
        }
    }

    ###########################################################

    function exibeIncricaoPetec3($idServidor) {
        /**
         * Verifica se o servidor está inscrito no respectivo petec
         */
        # Verifica se está inscrito
        if ($this->estaInscrito($idServidor, 8)) {
            p("Inscrito", "pHoraOk");
        } else {
            p("Não Inscrito", "pHorasFaltam");
        }
    }

    ###########################################################

    function exibeIncricaoPetec4($idServidor) {
        /**
         * Verifica se o servidor está inscrito no respectivo petec
         */
        # Verifica se está inscrito
        if ($this->estaInscrito($idServidor, 9)) {
            p("Inscrito", "pHoraOk");
        } else {
            p("Não Inscrito", "pHorasFaltam");
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

    function somatorioHoras8($idServidor) {
        /**
         * Informa o somatorio de horas do marcador 8
         * Petec - Portaria 518/26
         */
        $this->somatorioHorasPetec($idServidor, 8);
    }

    ###########################################################

    function somatorioHoras9($idServidor) {
        /**
         * Informa o somatorio de horas do marcador 9
         * Petec - Portaria 557/26
         */
        $this->somatorioHorasPetec($idServidor, 9);
    }

    ###########################################################
}
