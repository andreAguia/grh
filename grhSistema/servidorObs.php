<?php
/**
 * Observações do servidor
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Verifica a fase do programa
    $fase = get('fase','editar');
    
    $intra = new Intra();
    
    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh',FALSE);
    if($grh){
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Observações";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7,$idServidorPesquisado);
    }

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################

    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado); 

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    #$objeto->set_nome('');

    # select do edita
    $objeto->set_selectEdita('SELECT obs
                                FROM tbservidor
                               WHERE idServidor = '.$idServidorPesquisado);
    
    # Caminhos
    $objeto->set_linkGravar('?fase=gravar');
    #$objeto->set_linkListar('?');
    $objeto->set_linkListar('servidorMenu.php');

    # botão voltar
    $objeto->set_voltarForm('servidorMenu.php');

    # retira o botão incluir
    $objeto->set_botaoIncluir(FALSE);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbservidor');

    # Nome do campo id
    $objeto->set_idCampo('idServidor');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(
                        array ('linha' => 1,
                               'nome' => 'obs',
                               'autofocus' => TRUE,
                               'label' => 'Observações do Servidor',
                               'tipo' => 'textarea',
                               'size' => array(255,20))));
    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Observações do Servidor");
    $botaoRel->set_url("../grhRelatorios/servidorObs.php");
    $botaoRel->set_target("_blank"); 
    
    $objeto->set_botaoEditarExtra(array($botaoRel));
    
    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################
    switch ($fase)
    {
        case "editar" :            
        case "excluir" :	
        case "gravar" :
            $objeto->$fase($idServidorPesquisado);
            break;	
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}
