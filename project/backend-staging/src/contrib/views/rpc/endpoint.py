# vim: set fileencoding=utf-8 :

from .. import *
from ...models import Endpoint

rpc_endpoint = Blueprint('endpoint', __name__, url_prefix='/rpc/endpoint')


@rpc_endpoint.route('/register', methods=['POST'])
def register():
    endpoint_token = g.form.get('endpoint_token')
    if Endpoint.objects(endpoint_token=endpoint_token):
        return display(None, 409, 'Endpoint Token 已经存在')
    endpoint = Endpoint()
    for k, v in g.form.items():
        setattr(endpoint, k, v)
    endpoint.save()
    g.message = '设备注册成功'
    return display(None)


# vim:ts=4:sw=4
