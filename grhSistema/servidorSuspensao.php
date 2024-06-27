<?php

/**
 * Licença 
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Cadastro de suspensão";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio da área de Redução
    $origem = get_session("origem");

    # pega o idTpLicenca (se tiver)
    $idTpLicenca = soNumeros(get('idTpLicenca'));

    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro'))) {
        $parametro = retiraAspas(get_session('sessionParametro'));
    } else {
        $parametro = post('parametro');
        set_session('sessionParametro', $parametro);
    }

    # define a rotina em jscrit
    $script = '<script type="text/javascript" language="javascript">
        
            $(document).ready(function(){
            
                // Quando muda a data de término
                 $("#dtTermino").change(function(){
                    var dt1 = $("#dtInicial").val();
                    var dt2 = $("#dtTermino").val();
                    
                    data1 = new Date(dt1);
                    data2 = new Date(dt2);
                    
                    dias = (data2 - data1)/(1000*3600*24)+1;

                    $("#numDias").val(dias);
                  });                  

                 // Quando muda o período 
                 $("#numDias").change(function(){
                   
                    var dt1 = $("#dtInicial").val();
                    var numDias = $("#numDias").val();
                    
                    data1 = new Date(dt1);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));
                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");
            
                    $("#dtTermino").val(formatado);
                  });
                  
                // Quando muda a data Inicial
                $("#dtInicial").change(function(){
                   
                    var dt1 = $("#dtInicial").val();
                    var numDias = $("#numDias").val();
                    
                    data1 = new Date(dt1);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));
                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");
            
                    $("#dtTermino").val(formatado);
                  });
                  
                });
             </script>';

    # Começa uma nova página
    $page = new Page();

    # Jascript do formulário
    if ($fase == "editar") {
        $page->set_jscript($script);
    }

    # Jascript do upload
    if ($fase == "upload") {
        $page->set_ready('$(document).ready(function(){
                                $("form input").change(function(){
                                    $("form p").text(this.files.length + " arquivo(s) selecionado");
                                });
                            });');
    }
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Verifica se o Servidor tem direito a licença
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);

    if ($pessoal->get_perfilLicenca($idPerfil) == "Não") {
        $mensagem = 'Esse servidor está em um perfil que não pode ser suspenso';
        $alert = new Alert($mensagem);
        $alert->show();
        loadPage('servidorMenu.php');
    } else {

        # Abre um novo objeto Modelo
        $objeto = new Modelo();

        ################################################################
        # Exibe os dados do Servidor
        $objeto->set_rotinaExtra("get_DadosServidor");
        $objeto->set_rotinaExtraParametro($idServidorPesquisado);

        # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
        $objeto->set_nome('Suspensões');

        # botão de voltar da lista
        $objeto->set_voltarLista('servidorMenu.php');

        # select da lista
        $objeto->set_selectLista("SELECT dtInicial,
                                         numdias,
                                         ADDDATE(dtInicial,numDias-1),
                                         processo,
                                         idLicenca,
                                         idLicenca,
                                         obs,
                                         idLicenca
                                    FROM tblicenca
                                   WHERE idServidor = {$idServidorPesquisado}
                                     AND idTpLicenca = 26 
                               ORDER BY dtInicial desc");

        # select do edita
        $objeto->set_selectEdita("SELECT dtInicial,
                                         numDias,
                                         dtTermino,
                                         dtPublicacao,
                                         pgPublicacao,
                                         processo,
                                         obs,
                                         idTpLicenca,
                                         idServidor
                                    FROM tblicenca WHERE idLicenca = {$id}");

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
        $objeto->set_label(["Inicio", "Dias", "Término", "Processo", "Publicação", "Ver", "Obs"]);
        $objeto->set_width([12, 5, 12, 20, 12, 5, 25]);
        $objeto->set_align([null, null, null, null, null, null, "left"]);
        $objeto->set_funcao(['date_to_php', null, 'date_to_php']);
        $objeto->set_classe([null, null, null, null, "Licenca", "Suspensao"]);
        $objeto->set_metodo([null, null, null, null, "exibePublicacao", "exibePublicacaoPdf"]);

        # Classe do banco de dados
        $objeto->set_classBd('pessoal');

        # Nome da tabela
        $objeto->set_tabela('tblicenca');

        # Nome do campo id
        $objeto->set_idCampo('idLicenca');

        # Campos para o formulario
        $objeto->set_campos(array(
            array('nome' => 'dtInicial',
                'label' => 'Data Inicial:',
                'tipo' => 'data',
                'required' => true,
                'size' => 20,
                'col' => 3,
                'title' => 'Data do início.',
                'linha' => 1),
            array('nome' => 'numDias',
                'label' => 'Dias:',
                'tipo' => 'numero',
                'size' => 2,
                'title' => 'Número de dias.',
                'col' => 2,
                'linha' => 1),
            array('nome' => 'dtTermino',
                'label' => 'Data de Termino (opcional):',
                'tipo' => 'data',
                'size' => 20,
                'col' => 3,
                'title' => 'Data de Termino.',
                'linha' => 1),
            array('nome' => 'dtPublicacao',
                'label' => 'Data da Pub. no DOERJ:',
                'tipo' => 'data',
                'size' => 20,
                'title' => 'Data da Publicação no DOERJ.',
                'col' => 3,
                'linha' => 5),
            array('nome' => 'pgPublicacao',
                'label' => 'Página:',
                'tipo' => 'texto',
                'size' => 5,
                'title' => 'Página da públicação no DOERJ.',
                'col' => 2,
                'linha' => 5),
            array('nome' => 'processo',
                'label' => 'Processo:',
                'tipo' => 'processo',
                'size' => 30,
                'col' => 4,
                'title' => 'Número do Processo',
                'linha' => 5),
            array('linha' => 8,
                'nome' => 'obs',
                'label' => 'Observação:',
                'tipo' => 'textarea',
                'size' => array(80, 3)),
            array('nome' => 'idTpLicenca',
                'label' => 'idTpLicenca',
                'tipo' => 'hidden',
                'size' => 50,
                'padrao' => '26',
                'linha' => 9),
            array('nome' => 'idServidor',
                'label' => 'idServidor:',
                'tipo' => 'hidden',
                'padrao' => $idServidorPesquisado,
                'size' => 5,
                'linha' => 9)));

        # Log
        $objeto->set_idUsuario($idUsuario);
        $objeto->set_idServidorPesquisado($idServidorPesquisado);

        # Dados da rotina de Upload
        $pasta = PASTA_SUSPENSAO;
        $nome = "Publicação";
        $tabela = "tblicenca";
        $extensoes = ["pdf"];

        # Botão de Upload
        if (!empty($id)) {

            # Botão de Upload
            $botao = new Button("Upload {$nome}");
            $botao->set_url("servidorSuspensaoUpload.php?fase=upload&id={$id}");
            $botao->set_title("Faz o Upload do {$nome}");
            $botao->set_target("_blank");

            $objeto->set_botaoEditarExtra([$botao]);
        }
        ################################################################

        switch ($fase) {
            case "" :
            case "listar" :
            case "editar" :
                $objeto->$fase($id);
                break;

            case "excluir" :
                # Verifica se tem arquivo vinculado
                if (file_exists("{$pasta}{$id}.pdf")) {

                    # Verifica se existe a pasta dos arquivos apagados
                    if (!file_exists("{$pasta}_apagados/") || !is_dir("{$pasta}_apagados/")) {
                        mkdir("{$pasta}_apagados/", 0755);
                    }

                    # Move o arquivo para a pasta dos arquivos apagados
                    rename("{$pasta}{$id}.pdf", "{$pasta}_apagados/{$id}_" . $intra->get_usuario($idUsuario) . "_" . date("Y.m.d_H:i") . ".pdf");
                }

                # Exclui o registro
                $objeto->excluir($id);
                break;

            case "gravar" :
                $objeto->gravar($id, "servidorSuspensaoExtra.php");
                break;
        }
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}