# vim: set fileencoding=utf-8 :

from .. import *
from ...models import Hospital

rpc_spinner = Blueprint('spinner', __name__, url_prefix='/rpc/spinner')


@rpc_spinner.route('/price', methods=['POST'])
@auth_required('doctor')
def price():
    price = ()
    service = g.form.get('service')
    if service == 'consult':
        price = (0, 50, 100, 150, 200, 250, 300)
    elif service == 'clinic':
        price = (0, 100, 200, 300, 400, 500, 600, 700, 800, 900)
    elif service == 'phonecall':
        price = (-1, 0, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500)

    if g.run_mode == 'development' or g.role_instance.freeze == 'yes':
        price = price + (1, 0.1, 0.01)

    return display({'price': price})


@rpc_spinner.route('/quantity', methods=['POST'])
@auth_required('doctor')
def quantity():
    service = g.form.get('service')
    quantity = ()
    if service == 'clinic':
        quantity = (x for x in range(1, 11))
    elif service == 'consult':
        quantity = (x for x in range(1, 51))
    elif service == 'phonecall':
        quantity = (x for x in range(1, 11))
    return display({'quantity': quantity})


@rpc_spinner.route('/images', methods=['POST'])
@auth_required('doctor')
def images():
    alias = g.form.get('alias')
    default = [
        # 'http://api-staging.huimeibest.com/static/image/sample-1.jpg',
        # 'http://api-staging.huimeibest.com/static/image/sample-2.jpg',
        'http://api-staging.huimeibest.com/static/image/banner-1.png',
        'http://api-staging.huimeibest.com/static/image/banner-2.png',
    ]

    if alias == 'schedule-top':
        images = default
    elif alias == 'operation-top':
        images = default
    else:
        images = default
    return display({'images': images})


@rpc_spinner.route('/hospital', methods=['POST'])
@auth_required('doctor')
def hospital():
    keywords = g.form.get('keywords')
    if keywords:
        hospitals = Hospital.objects(name__contains=keywords).order_by('-name')
    else:
        hospitals = Hospital.objects().order_by('-order')
    if hospitals:
        if g.run_mode != 'development' and g.app_info.get('os') in ('ios') and g.app_version in ('3.0.0'):
            for i in hospitals:
                i.branches = []
        hospitals = [x.to_bson(fields=['id', 'name', 'address', 'branches']) for x in hospitals]
    return display({'hospitals': hospitals})


# vim:ts=4:sw=4
