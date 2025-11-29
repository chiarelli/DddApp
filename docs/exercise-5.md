# Exercise 5 — Migrations no Yii2

Branch: `exercise-5`

## Objetivos

- Reduzir consultas ao banco e custo de hidratação na listagem de clientes e suas pessoas relacionadas.
- Adicionar cache com invalidação automática e fragment cache por seção, mantendo consistência.

## Checklist

- [ ] Migration de índices + teste functional.
- [ ] Paginação/filtro (repo/use case/controller/view) + testes unit/functional.
- [ ] Seed grande + functional.
- [ ] Read-model asArray + DTOs + testes unit/functional.
- [ ] Interface/adapter de cache + use case (FileCache) + unit/functional.
- [ ] Behaviors de invalidação (Customer/Person/Pivot) + functional.
- [ ] Fragment cache por seção (bloco de cliente) + functional.
- [ ] Trocar provider para MemCache (compose + config) + smoke.
- [ ] Observabilidade/documentação + validação final.
