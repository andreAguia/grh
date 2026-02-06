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

    # Verifica a fase do programa
    $fase = get('fase', 'inicial');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    $idServidor = soNumeros(get('idServidor'));

    # Pega os parâmetros
    $parametroPetec = post('parametroPetec', get_session('$parametroPetec'));

    # Joga os parâmetros par as sessions
    set_session('parametroPetec', $parametroPetec);

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
            $linkVoltar = new Link("Voltar", "areaPetec.php");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Voltar para página anterior');
            $linkVoltar->set_accessKey('V');
            $menu1->add_link($linkVoltar, "left");

            # Faz Upload
            $linkUpload = new Link("Faz Upload de um novo arquivo", "?fase=upload");
            $linkUpload->set_class('button');
            $menu1->add_link($linkUpload, "right");

            $select = "SELECT idPetecImporta,
                          idServidor,
                          petec,
                          nome,
                          cpf,
                          obs,
                          erro
                     FROM tbpetecimporta
                     WHERE erro IS NOT NULL 
                     ORDER BY obs";

            $pessoal = new Pessoal();
            $result2 = $pessoal->select($select);
            $contador1 = $pessoal->count($select);

            # Importar
            if ($contador1 == 0) {
                $botaoImportar = new Link("Grava no banco de dados", "?fase=gravaBd");
                $botaoImportar->set_class('button');
                $botaoImportar->set_title('Faz a importação do petec');
                $menu1->add_link($botaoImportar, "right");
            }

            $menu1->show();

            $grid->fechaColuna();

            ###             

            $grid->abreColuna(9);

            ### Com Erros
            $select = "SELECT idPetecImporta,
                          idServidor,
                          petec,
                          nome,
                          cpf,
                          obs,
                          erro
                     FROM tbpetecimporta
                     WHERE erro IS NOT NULL 
                     ORDER BY obs";

            $pessoal = new Pessoal();
            $result2 = $pessoal->select($select);
            $contador1 = $pessoal->count($select);

            $tabela = new Tabela();
            $tabela->set_titulo("Arquivo CSV");
            $tabela->set_subtitulo("Registros Com Erros");
            $tabela->set_conteudo($result2);

            $tabela->set_label(["id", "idServidor", "Petec", "Nome", "Cpf", "Obs", "Erro"]);
            $tabela->set_align(["center", "center", "center", "left", "center", "left", "left"]);

            $tabela->set_editar('?fase=editaRegistro');
            $tabela->set_idCampo('idPetecImporta');

            $tabela->set_excluir('?fase=excluiRegistro&id=');
            $tabela->set_idCampo('idPetecImporta');
            $tabela->show();

            ### Sem Erros
            $select = "SELECT idPetecImporta,
                          idServidor,
                          petec,
                          nome,
                          cpf,
                          obs
                     FROM tbpetecimporta
                     WHERE erro IS NULL 
                     ORDER BY obs";

            $pessoal = new Pessoal();
            $result2 = $pessoal->select($select);
            $contador2 = $pessoal->count($select);

            $tabela = new Tabela();
            $tabela->set_titulo("Arquivo CSV");
            $tabela->set_subtitulo("Registros Sem Erros");
            $tabela->set_conteudo($result2);

            $tabela->set_label(["id", "idServidor", "Petec", "Nome", "Cpf", "Obs"]);
            $tabela->set_align(["center", "center", "center", "left", "center", "left"]);
            $tabela->show();

            $grid->fechaColuna();

            ### Resumo

            $grid->abreColuna(3);

            $tabela2 = new Tabela();
            $tabela2->set_titulo("Importação");
            $tabela2->set_conteudo([
                ["Com Erros", $contador1],
                ["SEM Erros", $contador2],
            ]);

            $tabela2->set_label(["Informação", "Quantidade"]);
            $tabela2->set_align(["center", "center"]);
            $tabela2->show();

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
            echo "<form class='upload' method='post' enctype='multipart/form-data'><br>
                        <input type='file' name='doc'>
                        <p>Click aqui ou arraste o arquivo.</p>
                        <button type='submit' name='submit'>Enviar</button>
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

            $texto = "Extensões Permitidas:";

            foreach ($extensoes as $pp) {
                $texto .= " $pp";
            }
            $texto .= "<br/>Tamanho Máximo do Arquivo: $limite M";

            br();
            p($texto, "f14", "center");

            if ((isset($_POST["submit"])) && (!empty($_FILES['doc']))) {
                $upload = new UploadDoc($_FILES['doc'], $pasta, "petec", $extensoes);

                # Salva e verifica se houve erro
                if ($upload->salvar()) {

                    # Registra log
                    $Objetolog = new Intra();
                    $data = date("Y-m-d H:i:s");
                    $atividade = "Importou o arquivo csv de férias do SigRh temporáriamente para memória";
                    $Objetolog->registraLog($idUsuario, $data, $atividade, null, null, 8);

                    # Volta para o menu
                    loadPage("?fase=importar0");
                } else {
                    loadPage("?fase=importar");
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

            # Apaga a tabela 
            $select = 'SELECT idPetecImporta
                         FROM tbpetecimporta';

            $row = $pessoal->select($select);

            $pessoal->set_tabela("tbpetecimporta");
            $pessoal->set_idCampo("idPetecImporta");

            foreach ($row as $tt) {
                $pessoal->excluir($tt[0]);
            }

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
            $arquivo = "../_temp/petec.csv";

            # Altere o divisor de acordo com o arquivo
            #$divisor = ";";
            $divisor = get_arquivoDivisor($arquivo);

            # Flags
            $certos = 0;
            $linhas = 0;

            # Verifica a existência do arquivo
            if (file_exists($arquivo)) {
                $lines = file($arquivo);
                $linhaDados = false;

                # Campos
                $campos = array(
                    "idServidor",
                    "petec",
                    "nome",
                    "cpf",
                    "obs",
                    "erro"
                );

                # Percorre o arquivo e guarda os dados em um array
                foreach ($lines as $linha) {

                    # Separa os valores
                    $colunas = explode($divisor, $linha);

                    # Verifica se é cabeçalho
                    if ($colunas[0] <> '﻿Carimbo de data/hora') {

                        # Verifica se é cabeçalho ou dado
                        if (isset($colunas[$colunaCpf]) AND isset($colunas[$colunaNome])) {

                            # Trata os valores
                            $cpf = soNumeros($colunas[$colunaCpf]);
                            $nome = $colunas[$colunaNome];
                            $petec = "petec1";

                            # Campos em branco
                            $obs = null;
                            $erroMsg = null;
                            $idServidor = null;

                            # Verifica se o cpf é número
                            if (is_numeric($cpf) AND !empty($cpf)) {

                                $valor = array(
                                    $idServidor,
                                    $petec,
                                    retiraAspas($nome),
                                    formatCnpjCpf($cpf),
                                    $obs,
                                    $erroMsg
                                );

                                # Grava
                                $pessoal->gravar($campos, $valor, null, "tbpetecimporta", "idPetecImporta");
                            } else {
                                echo "Cpf não numérico!";
                            }
                        }
                    } else {
                        # Define as colunas
                        $colunaIdfuncional = 0;
                        $colunaNome = array_search('Nome:', $colunas);
                        $colunaCpf = array_search('CPF:', $colunas);
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

            # Inicia a variável de erro
            $erro = 0;
            $importado = 0;
            $petec = "petec1";

            # Monta o Select
            $select = "SELECT idPetecImporta,
                          idServidor,
                          petec,
                          nome,
                          cpf,
                          obs,
                          erro
                     FROM tbpetecimporta
                     ORDER BY idPetecImporta";

            $pessoal = new Pessoal();
            $result2 = $pessoal->select($select);

            foreach ($result2 as $item) {
                # Pega o idServidor desse CPF
                $cpf = $item["cpf"];
                $nome = $item["nome"];

                $idPessoa = $pessoal->get_idPessoaCPF(formatCnpjCpf($cpf));
                $idServidor = $pessoal->get_idServidoridPessoa($idPessoa);

                if (empty($idPessoa)) {
                    $obs = null;
                    $erroMsg = "ERRO - CPF nã\o Encontrado";
                    $erro++;
                } else {
                    $obs = "OK - Encontrado -> {$pessoal->get_nome($idServidor)}";
                    $erroMsg = null;
                    $importado++;
                }
                # Campos
                $campos = array(
                    "idServidor",
                    "petec",
                    "nome",
                    "cpf",
                    "obs",
                    "erro"
                );

                $valor = array(
                    $idServidor,
                    $petec,
                    retiraAspas($nome),
                    formatCnpjCpf($cpf),
                    $obs,
                    $erroMsg
                );

                # Grava
                $pessoal->gravar($campos, $valor, $item["idPetecImporta"], "tbpetecimporta", "idPetecImporta");
                br();
            }

            alert("{$erro} Erros Encontrados - {$importado} - Itens importados");
            loadPage("?fase=exibeTabela");
            break;

        ################################################# 

        case "editaRegistro" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar", "?");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Voltar para página anterior');
            $linkVoltar->set_accessKey('V');
            $menu1->add_link($linkVoltar, "left");

            $menu1->show();

            titulotable("Editar Registro do Arquivo CSV");
            br();

            # Pega os dados
            $select = "SELECT nome,
                              cpf,
                              idPetecImporta
                         FROM tbpetecimporta
                        WHERE idPetecImporta = {$id}";

            $result = $pessoal->select($select, false);

            # Verifica se tem erro
            if (!empty($result["erro"])) {
                tituloTable($result["erro"]);
                callout($result["obs"]);
                br();
            }

            # Monta o formulário
            $form = new Form("?fase=validaRegistro&id={$id}");

            # nome
            $controle = new Input('nome', 'texto', 'Nome:', 1);
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_col(6);
            $controle->set_autofocus(true);
            $controle->set_title('A idfuncional do Servidor');
            $controle->set_valor($result['nome']);
            $form->add_item($controle);

            # cpf
            $controle = new Input('cpf', 'texto', 'Cpf:', 1);
            $controle->set_size(20);
            $controle->set_linha(2);
            $controle->set_col(6);
            $controle->set_valor($result['cpf']);
            $form->add_item($controle);
            
            # idPetecImporta
            $controle = new Input('idPetecImporta', 'hidden', 'idPetecImporta:', 1);
            $controle->set_size(20);
            $controle->set_linha(2);
            $controle->set_col(6);
            $controle->set_valor($result['idPetecImporta']);
            $form->add_item($controle);

            # submit
            $controle = new Input('submit', 'submit');
            $controle->set_valor('Salvar');
            $controle->set_linha(3);
            $form->add_item($controle);

            $form->show();
            break;

        #################################################  
            
        case "validaRegistro" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar", "#");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Voltar para página anterior');
            $linkVoltar->set_accessKey('V');
            $menu1->add_link($linkVoltar, "left");

            $menu1->show();

            titulotable("Editar Registro");
            br(6);

            aguarde("Salvando ...");

            # Pega os dados
            $nome = post("nome");
            $cpf = post("cpf");
            $idPetecImporta = post("idPetecImporta");

            $campos = array(
                "nome",
                "cpf"
            );

            # Valores
            $valor = array(
                $nome,
                $cpf
            );

            # Grava
            $pessoal->gravar($campos, $valor, $idPetecImporta, "tbpetecimporta", "idPetecImporta");

            loadPage("?fase=analisaTabela");
            break;

        #################################################  

        case "excluiRegistro" :

            br(5);
            aguarde("Excluindo ...");

            # Exclui o registro
            $pessoal->excluir($id, "tbpetecimporta", "idPetecImporta");

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

