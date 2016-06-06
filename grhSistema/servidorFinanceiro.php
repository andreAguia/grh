<?php
/**
 * Histórico de Triênios
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$matricula = null;		  # Reservado para a matrícula do servidor logado
$matriculaGrh = null;		  # Reservado para a matrícula pesquisada

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($matricula,13);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Botão voltar
    botaoVoltar('servidorMenu.php');
    
    # Exibe os dados do servidor
    Grh::listaDadosServidor($matriculaGrh);

    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);
    
    # Pega os dados financeiros
    $salario = $pessoal->get_salarioBase($matriculaGrh);
    $trienio = $pessoal->get_trienioValor($matriculaGrh);
    $comissao = $pessoal->get_salarioCargoComissao($matriculaGrh);
    $gratificacao = $pessoal->get_gratificacao($matriculaGrh);
    $cessao = $pessoal->get_salarioCessao($matriculaGrh);
    $total = $salario+$trienio+$comissao+$gratificacao+$cessao;

    # Dados da tabela
    $conteudo = array(array('Salário:',$salario),
                      array('Triênio:',$trienio),
                      array('Cargo em Comissão:',$comissao),
                      array('Gratificação Especial:',$gratificacao),
                      array('Salário recebido pelo Órgão de Origem (Cedidos):',$cessao),
                      array('Total',$total));

    $label = array("Descrição","Valor");
    $width = array(60,40);
    $align = array("left","right");
    $function = array (null,"formataMoeda");

    $formatacaoCondicional = array(array('coluna' => 0,
                                         'valor' => 'Total',
                                         'operador' => '=',
                                         'id' => 'total'));

    # Exibe o título
    $top = new TopBar('Resumo Financeiro');
    $top->set_title('Resumo Financeiro');
    $top->show();
        
    # Monta a tabela
    $tabela = new Tabela();
    #$tabela->set_titulo('Resumo Financeiro');
    $tabela->set_conteudo($conteudo);
    $tabela->set_cabecalho($label,$width,$align);
    $tabela->set_funcao($function);
    #$tabela->set_link($link);
    $tabela->set_totalRegistro(false);
    #$tabela->set_idCampo('matricula');
    $tabela->set_formatacaoCondicional($formatacaoCondicional);
    $tabela->show();
    
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    $page->terminaPagina();
}
