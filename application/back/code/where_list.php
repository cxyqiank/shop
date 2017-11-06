$filter_%field% = input('filter_%field%','');
if(""!== $filter_%field%)
{
$search->where('%field%','like','%'.$filter_%field%.'%');
$filter['filter_%field%'] = $filter_%field%;
}
