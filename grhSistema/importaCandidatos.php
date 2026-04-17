<?php

/**
 * Importa Petec
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
    $candidato = new CandidatoAdm2025();

    # Verifica a fase do programa
    $fase = get('fase', 'inicial');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    $idServidor = soNumeros(get('idServidor'));

    # Pega os parâmetros
    $idConcurso = get_session("idConcurso");

    # Começa uma nova página
    $page = new Page();
    if ($fase == "upload") {
        $page->set_ready('$(document).ready(function(){
                                $("form input").change(function(){
                                    $("form p").text(this.files.length + " arquivo(s) selecionado");
                                });
                            });');
    }
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio") {
        AreaServidor::cabecalho();
    }

    # Limita o tamanho da tela
    $grid1 = new Grid();
    $grid1->abreColuna(12);

    switch ($fase) {

        /*
         * exibeTabela
         */

        case 'inicial':
        case "exibeTabela" :

            $grid = new Grid();
            $grid->abreColuna(12);

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar", "cadastroCandidatosAdm2025.php");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Voltar para página anterior');
            $linkVoltar->set_accessKey('V');
            $menu1->add_link($linkVoltar, "left");

            # Verifica se tem dados na tabela
            $numReg = $candidato->get_numRegistrosTabelaUpload();

            # Faz Upload
            if ($numReg == 0) {
                $linkUpload = new Link("Faz Upload de arquivo CSV", "?fase=upload");
                $linkUpload->set_class('button');
                $menu1->add_link($linkUpload, "right");
            }

            # Apaga
            if ($numReg > 0) {
                $linkApaga = new Link("Apaga o Arquivo CSV", "?fase=apagaTabela");
                $linkApaga->set_class('alert button');
                $linkApaga->set_confirma('Deseja mesmo apagar a tabela da memória?');
                $menu1->add_link($linkApaga, "right");
            }


            # Monta o select
            $select = "SELECT tbcandidato.classifAc,
                              tbcandidato.inscricao,
                              tbcandidato.nome,
                              tbcandidatoimporta.nome,
                              tbcandidato.idCandidato,
                              tbcandidatoimporta.idCandidato
                         FROM tbcandidato JOIN tbcandidatoimporta USING (inscricao)
                         
                     ORDER BY classif";

            $pessoal = new Pessoal();
            $result2 = $pessoal->select($select);
            $contador2 = $pessoal->count($select);

            # Importar - somente se tiver arquivo na memória
            if ($contador2 > 0) {
                $botaoImportar = new Link("Gravar no banco de dados", "?fase=gravaBdPetec1Inicio");
                $botaoImportar->set_class('button warning');
                $botaoImportar->set_title('Faz a importação dos Candidatos');
                $menu1->add_link($botaoImportar, "right");
            }

            $menu1->show();

            $tabela = new Tabela();
            $tabela->set_titulo("Arquivo CSV");
            $tabela->set_subtitulo("Registros SEM Erros");
            $tabela->set_conteudo($result2);

            $tabela->set_label(["#", "Inscrição", "Nome", "Busca pelo nº de Inscrição", "IdCandidato BD", "IdCandidato CSV"]);
            $tabela->set_align(["center", "center", "left", "left"]);

//            $tabela->set_classe([null, null, null, "Candidato"]);
//            $tabela->set_metodo([null, null, null, "get_candidato"]);
//            $tabela->set_editar('?fase=editaRegistro');
//            $tabela->set_idCampo('idCandidatoImporta');
//
//            $tabela->set_excluir('?fase=excluiRegistro&id=');
//            $tabela->set_idCampo('idCandidatoImporta');
            $tabela->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        #################################################    

        /*
         * Upload do arquivo
         */

        case "upload" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar", "?fase=exibeTabela");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Voltar para página anterior');
            $linkVoltar->set_accessKey('V');
            $menu1->add_link($linkVoltar, "left");

            $menu1->show();

            tituloTable("Faz Upload de um arquivo");

            $grid = new Grid();
            $grid->abreColuna(12);

            $grid->fechaColuna();
            $grid->fechaGrid();

            $grid = new Grid("center");
            $grid->abreColuna(6);

            # Gera a área de upload
            echo "<form class = 'upload' method = 'post' enctype = 'multipart/form-data'><br>
                    <input type = 'file' name = 'doc'>
                    <p>Click aqui ou arraste o arquivo.</p>
                    <button type = 'submit' name = 'submit'>Enviar</button>
                    </form>";

            $pasta = "../_temp/";

            # Se não existe o programa cria
            if (!file_exists($pasta) || !is_dir($pasta)) {
                mkdir($pasta, 0755);
            }

            # Extensões possíveis
            $extensoes = array("csv");

            # Pega os valores do php.ini
            $postMax = limpa_numero(ini_get('post_max_size'));
            $uploadMax = limpa_numero(ini_get('upload_max_filesize'));
            $limite = menorValor(array($postMax, $uploadMax));

            $texto = "Extensões Permitidas: ";

            foreach ($extensoes as $pp) {
                $texto .= " $pp";
            }
            $texto .= "<br/>Tamanho Máximo do Arquivo: $limite M";

            br();
            p($texto, "f14", "center");

            if ((isset($_POST["submit"])) && (!empty($_FILES['doc']))) {
                $upload = new UploadDoc($_FILES['doc'], $pasta, "candidato", $extensoes);

                # Salva e verifica se houve erro
                if ($upload->salvar()) {

                    # Registra log
                    $Objetolog = new Intra();
                    $data = date("Y-m-d H: i: s");
                    $atividade = "Importou o arquivo csv dos Candidatos";
                    #$Objetolog->registraLog($idUsuario, $data, $atividade, null, null, 8);
                    # Volta para o menu
                    loadPage("?fase=importar0");
                } else {
                    loadPage("?fase=upload");
                }
            }
            $grid->fechaGrid();
            break;

        #################################################    

        /*
         * Exclui a tabela csv que porventura esteja cadastrado
         */

        case "importar0" :

            br(5);
            aguarde("Preparando ...");
            loadPage("?fase=importar1");
            break;

        #################################################    
        /*

          /*
         * Exclui a tabela csv que porventura esteja cadastrado
         */

        case "importar1" :

            br(5);
            aguarde("Liberando Espaço na Memória");

            $candidato->apagaTabelaCsv();

            loadPage("?fase=importar2");
            break;

        #################################################    
        /*
         * Exibe a mensagem para o usuário
         */
        case "importar2" :

            br(5);
            aguarde("Fazendo o upload do arquivo");

            sleep(3);
            loadPage("?fase=importar3");
            break;

        #################################################

        /*
         * Salva o arquivo csv na tabela temporária para análise
         */

        case "importar3" :

            # Define o arquivo a ser importado
            $arquivo = "../_temp/candidato.csv";

            # Altere o divisor de acordo com o arquivo
            $divisor = ";";

            # Flags
            $certos = 0;
            $linhas = 0;

            # Verifica a existência do arquivo
            if (file_exists($arquivo)) {
                $lines = file($arquivo);
                $linhaDados = false;

                # Campos
                $campos = array(
                    "nome",
                    "inscricao",
                    "classif",
                    "idCandidato"
                );

                # Inicia o contador de linhas
                $contadorLinhas = 0;

                # Percorre o arquivo e guarda os dados em um array
                foreach ($lines as $linha) {

                    # Incrementa a linha
                    $contadorLinhas++;

                    # Separa os valores
                    $colunas = explode($divisor, $linha);

                    #var_dump($colunas);
                    # Verifica se é cabeçalho
                    if ($contadorLinhas == 1) {

                        # Não faz nada
                        # Só pula essa linhoa
                        # Pois é a do cabeçalho
                    } else {

                        # Monta o array de gravação de acordo com a ordem da planilha
                        $valor = array(
                            $colunas[0],
                            $colunas[1],
                            $colunas[2],
                            $candidato->get_idCandidato($colunas[1])
                        );

                        # Grava
                        $pessoal->gravar($campos, $valor, null, "tbcandidatoimporta", "idCandidatoImporta");
                    }
                }
            }

            loadPage("?fase=analisaTabela");
            break;

        #################################################

        case "analisaTabela" :

            /*
             * Analisa a tabela
             */

            br(5);
            aguarde("Verificando erros");

            loadPage("?fase=analisaTabela1");
            break;

        #################################################

        case "analisaTabela1" :

            /*
             * Analisa a tabela
             * Analisa o banco de dados temporário e faz a análise dele
             */


            loadPage("?fase=exibeTabela");
            break;

        ################################################# 

        case "gravaBdPetec1Inicio" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar", "?fase=exibeTabela");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Voltar para página anterior');
            $linkVoltar->set_accessKey('V');
            $menu1->add_link($linkVoltar, "left");

            $menu1->show();

            br(5);
            p("Informe Para Qual COTA será importado");

            # Formulário
            $form = new Form('?fase=gravaBdPetec1');

            # Cotas
            $controle = new Input('parametroCota', 'combo', 'Cota:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Cota');
            $controle->set_array([
                ["classifAc", "Ampla Concorrência"],
                ["classifPcd", "PCD"],
                ["classifNi", "Negros e Indígenas"],
                ["classifHipo", "Hipossuficiente Econômico"]
            ]);

            $controle->set_linha(1);
            $controle->set_col(5);
            $form->add_item($controle);

            # submit
            $controle = new Input('submit', 'submit');
            $controle->set_valor('Gravar no Banco de Dados');
            $controle->set_linha(3);
            $controle->set_tabIndex(3);
            $controle->set_accessKey('E');
            $form->add_item($controle);

            $form->show();
            break;

        #################################################   

        case "gravaBdPetec1Aviso" :

            br(5);
            aguarde("Gravando ...");

            loadPage("?fase=gravaBdPetec1");
            break;

        #################################################  

        case "gravaBdPetec1" :

            $parametroCota = post("parametroCota");

            $select = "SELECT * 
                         FROM tbcandidatoimporta";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select);

            foreach ($row as $tt) {

                $campos = array(
                    $parametroCota
                );

                $valor = array(
                    $tt["classif"]
                );

                # Grava
                $pessoal->gravar($campos, $valor, $tt["idCandidato"], "tbcandidato", "idCandidato");
            }

            # Registra log
            $Objetolog = new Intra();
            $data = date("Y-m-d H:i:s");
            $atividade = "Importou o arquivo csv de candidatos para o banco de dados";
            $Objetolog->registraLog($idUsuario, $data, $atividade, null, null, 8);

            loadPage("?");
            break;

        #################################################

        /*
         * Exclui a tabela csv que porventura esteja cadastrado
         */

        case "apagaTabela" :

            br(5);
            aguarde("Apagando ...");

            loadPage("?fase=apagaTabela2");
            break;

        #################################################      

        case "apagaTabela2" :

            $candidato->apagaTabelaCsv();

            # Registra log
            $Objetolog = new Intra();
            $data = date("Y-m-d H:i:s");
            $atividade = "Excluiu o arquivo csv dos candidatos";
            $Objetolog->registraLog($idUsuario, $data, $atividade, null, null, 3);

            loadPage("?");
            break;

        #################################################  
    }

    $grid1->fechaColuna();
    $grid1->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}

