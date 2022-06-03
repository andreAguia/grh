<?php

/**
 * Cadastro de MCF
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
    $fase = get('fase', 'listar');

    # Cria um array com os anos possíveis
    $anoInicial = 1999;
    $anoAtual = date('Y');
    $ano = arrayPreenche($anoInicial, $anoAtual, "d");

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de MCF";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro'))) {     # Se o parametro não vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro')); # passa o parametro da session para a variavel parametro retirando as aspas
    } else {
        $parametro = post('parametro');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametro', $parametro);    # transfere para a session para poder recuperá-lo depois
    }

    # Começa uma nova página
    $page = new Page();
    if ($fase == "uploadMcf") {
        $page->set_ready('$(document).ready(function(){
                                $("form input").change(function(){
                                    $("form p").text(this.files.length + " arquivo(s) selecionado");
                                });
                            });');
    }
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo
    $objeto->set_nome('Mapa de Controle de Frequência - MCF');

    # Botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # select da lista
    $objeto->set_selectLista('SELECT idMcf,
                                      ano,
                                      mes,
                                      obs,
                                      idMcf,
                                      idMcf
                                 FROM tbmcf
                                WHERE ano LIKE "%' . $parametro . '%"
                                   OR obs LIKE "%' . $parametro . '%" 
                             ORDER BY ano desc,mes desc');

    # select do edita
    $objeto->set_selectEdita('SELECT ano,
                                     mes,
                                     obs
                                FROM tbmcf
                               WHERE idMcf = ' . $id);

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(["Id", "Ano", "Mês", "Obs", " Ver"]);
    $objeto->set_width([5, 10, 10, 65, 5]);
    $objeto->set_align(["center", "center", "center", "left"]);
    $objeto->set_funcao([null, null, "get_nomeMes"]);

    $objeto->set_classe([null, null, null, null, "Pessoal"]);
    $objeto->set_metodo([null, null, null, null, "exibeMcf"]);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbmcf');

    # Nome do campo id
    $objeto->set_idCampo('idMcf');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);
    
    # Pega o ultimo mês cadastrado
    $mcf = new Mcf();
    $ultimoMes = $mcf->getUltimoMesCadastrado();
    $ultimoAno = $mcf->getUltimoAnoCadastrado();
    
    # Acerta o mês
    if($ultimoMes < "12"){
        $ultimoMes++;
    }else{
        $ultimoMes = "1";
        $ultimoAno++;
    }            
    
    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'ano',
            'label' => 'Ano:',
            'tipo' => 'combo',
            'array' => $ano,
            'required' => true,
            'autofocus' => true,
            'padrao' => $ultimoAno,
            'col' => 3,
            'size' => 30),
        array('linha' => 1,
            'nome' => 'mes',
            'label' => 'Mes:',
            'tipo' => 'combo',
            'array' => $mes,
            'required' => true,
            'padrao' => $ultimoMes,
            'col' => 3,
            'size' => 30),
        array('linha' => 1,
            'nome' => 'obs',
            'label' => 'Obs:',
            'tipo' => 'texto',
            'col' => 6,
            'size' => 80)));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    # Botão de Upload 
    if (!empty($id)) {

        # Monta o arquivo
        $arquivo = PASTA_MCF . "{$id}.pdf";

        # Botão de Upload
        $botao = new Button("Upload do PDF do Mcf");
        $botao->set_url("?fase=uploadMcf&id={$id}");
        $botao->set_title("Faz o Upload do PDF do Mcf");
        $botao->set_target("_blank");

        $objeto->set_botaoEditarExtra([$botao]);
    }

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            $objeto->listar();
            break;

        case "editar" :
        case "gravar" :
            $objeto->$fase($id);
            break;

        case "excluir" :

            # Verifica se tem PDF e apaga o PDF
            if (file_exists(PASTA_MCF . "{$id}.pdf")) {
                unlink(PASTA_MCF . "{$id}.pdf");
            }
            $objeto->$fase($id);
            break;

        ################################################################

        case "uploadMcf" :
            $grid = new Grid("center");
            $grid->abreColuna(12);

            # Pasta onde será guardado o arquivo
            $pasta = PASTA_MCF;

            # Nome da rotina de upload
            $rotinaUpload = "?fase=uploadMcf&id={$id}";

            # Extensões possíveis
            $extensoes = ["pdf"];

            # Botão voltar
            if (!file_exists("{$pasta}{$id}.pdf")) {
                br();

                # Título
                tituloTable("Upload MCF");

                # do Log
                $atividade = "Fez o upload do Bim de uma licença médica";
            } else {
                # Monta o Menu
                $menu = new MenuBar();

                $botaoApaga = new Button("Apagar o MCF");
                $botaoApaga->set_url("?fase=apagaMcf&id={$id}");
                $botaoApaga->set_title("Apaga o arquivo PDF do MCF cadastrado");
                $botaoApaga->set_class("button alert");
                $botaoApaga->set_confirma('Tem certeza que vc deseja apagar o documento PDF deste MCF?');
                $menu->add_link($botaoApaga, "right");
                $menu->show();

                # Título
                tituloTable("Substituir o MCF Cadastrado");

                # Define o link de voltar após o salvar
                $voltarsalvar = "?fase=uploadTerminado";

                # do Log
                $atividade = "Substituiu o arquivo PDF do MCF";
            }

            #####
            # Limita a tela
            $grid->fechaColuna();
            $grid->abreColuna(6);

            # Monta o formulário
            echo "<form class='upload' method='post' enctype='multipart/form-data'><br>
                        <input type='file' name='doc'>
                        <p>Click aqui ou arraste o arquivo.</p>
                        <button type='submit' name='submit'>Enviar</button>
                    </form>";

            # Se não existe o programa cria
            if (!file_exists($pasta) || !is_dir($pasta)) {
                mkdir($pasta, 0755);
            }

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
                $upload = new UploadDoc($_FILES['doc'], $pasta, $id, $extensoes);

                # Salva e verifica se houve erro
                if ($upload->salvar()) {

                    # Registra log
                    $Objetolog = new Intra();
                    $data = date("Y-m-d H:i:s");
                    $Objetolog->registraLog($idUsuario, $data, $atividade, null, $id, 8, $idServidorPesquisado);

                    # Fecha a janela aberta
                    loadPage("?fase=uploadTerminado");
                } else {
                    # volta a tela de upload
                    loadPage("?fase=uploadBim&id=$id");
                }
            }

            # Informa caso exista um arquivo com o mesmo nome
            $arquivoDocumento = $pasta . $id . ".pdf";
            if (file_exists($arquivoDocumento)) {
                p("Já existe um documento para este registro!!<br/>"
                        . "O novo documento irá substituir o antigo !", "puploadMensagem");
                br();
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        case "uploadTerminado" :
            # Informa que o bim foi substituído
            alert("PDF Cadastrado !!");

            # Fecha a janela
            echo '<script type="text/javascript" language="javascript">window.close();</script>';
            break;

        ################################################################

        case "apagaMcf" :

            # Apaga o arquivo
            if (unlink(PASTA_MCF . "{$id}.pdf")) {
                alert("PDF Excluído !!");

                # Registra log
                $atividade = "Excluiu o arquivo PDF do Bim";
                $Objetolog = new Intra();
                $data = date("Y-m-d H:i:s");
                $Objetolog->registraLog($idUsuario, $data, $atividade, null, $id, 3, $idServidorPesquisado);

                # Fecha a janela
                echo '<script type="text/javascript" language="javascript">window.close();</script>';
            } else {
                alert("Houve algum problema, O arquivo não pode ser excluído !!");

                # Fecha a janela
                echo '<script type="text/javascript" language="javascript">window.close();</script>';
            }

            break;

        ################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}