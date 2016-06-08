<?php
/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$matricula = null;		  # Reservado para a matrícula do servidor logado

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($matricula,13);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######
    
    $select ='SELECT tbfuncionario.matricula,
                     tbfuncionario.idfuncional,
                     tbpessoa.nome,                 
                     tbfuncionario.matricula,
                     tbfuncionario.matricula,
                     tbfuncionario.matricula,
                     tbfuncionario.matricula,
                     tbfuncionario.matricula,
                     tbfuncionario.matricula,
                     tbperfil.nome
                FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                   LEFT JOIN tbperfil ON (tbfuncionario.idPerfil = tbperfil.idperfil)
               WHERE tbfuncionario.Sit = 1 
            ORDER BY tbfuncionario.matricula';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral Financeiro');
    $relatorio->set_subtitulo('Agrupado por Perfil - Ordenados por Matricula');

    $relatorio->set_label(array('Matricula','Id','Nome','Salário','Triênio','Comissão','Gratificação Especial','Salário Cedidos','Total','Perfil'));
    $relatorio->set_width(array(10,5,25,10,10,10,10,10,10,0));
    $relatorio->set_align(array("center","center","left","right","right","right","right","right","right"));
    $relatorio->set_funcao(array("dv",null,null,"formataMoeda","formataMoeda","formataMoeda","formataMoeda","formataMoeda","formataMoeda"));

    $classe = array("","","","pessoal","pessoal","pessoal","pessoal","pessoal","pessoal");
    $metodo = array("","","","get_salarioBase","get_trienioValor","get_salarioCargoComissao","get_gratificacao","get_salarioCessao","get_salarioTotal");

    $relatorio->set_classe($classe);
    $relatorio->set_metodo($metodo);
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(9);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}
?>
