# vim: set fileencoding=utf-8 :

from .. import *
from ...models import Release

rpc_release = Blueprint('release', __name__, url_prefix='/rpc/release')


@rpc_release.route('/latest', methods=['POST'])
def latest():
    device_type = g.form.get('device_type')
    latest = Release.objects(device_type=device_type).first_or_404()
    return display({'latest': latest.to_bson()})


# vim:ts=4:sw=4
